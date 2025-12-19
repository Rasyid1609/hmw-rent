<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Loan;
use Inertia\Response;
use App\Enums\MessageType;
use App\Models\FineSetting;
use Illuminate\Http\Request;
use App\Models\ReturnProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Enums\ReturnProductCondition;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\Admin\ReturnProductResource;



class ReturnProductController extends Controller
{
    public function index(): Response
    {
        $return_products = ReturnProduct::query()
            ->select(['id', 'return_product_code', 'status', 'loan_id', 'user_id', 'product_id', 'return_date', 'created_at'])
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->with(['product', 'fine', 'loan', 'user', 'returnProductCheck'])
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('Admin/ReturnProducts/Index', [
            'page_settings' => [
                'title' => 'Pengembalian',
                'subtitle' => 'Menampilkan semua data pengembalian yang tersedia pada platform ini. '
            ],
            'return_products' => ReturnProductResource::collection($return_products)->additional([
                'meta' => [
                    'has_pages' => $return_products->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load' => 10
            ],
            'conditions' => ReturnProductCondition::options(),
        ]);
    }

    public function create(Loan $loan): Response|RedirectResponse
    {
        if ($loan->returnProduct()->exists()){
            return to_route('admin.loans.index');
        }

        if (!FineSetting::first()){
            return to_route('admin.fine-settings.create');
        }

        return inertia('Admin/ReturnProducts/Create', [
            'page_settings' => [
                'title' => 'Pengembalian Barang',
                'subtitle' => 'Kembalikan Barang yang dipinjam disini. Klik kembalikan setelah selesai',
                'method' => 'POST',
                'action' => route('admin.return-products.store', $loan),
            ],
            'loan' => $loan->load([
                'user',
                'product' => fn($query) => $query->with('publisher'),
            ]),
            'date' => [
                'return_date' => Carbon::now()->toDateString(),
            ],

            'conditions' => ReturnProductCondition::options(),
        ]);
    }

    public function store(Loan $loan, ReturnProductRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $return_product = $loan->returnProduct()->create([
                'return_product_code' => str()->lower(str()->random(10)),
                'product_id' => $loan->product_id,
                'user_id' => $loan->user_id,
                'return_date' => Carbon::today(),
            ]);

            $return_product_check = $return_product->returnProductCheck()->create([
                'condition' => $request->condition,
                'notes' => $request->notes,
            ]);

            match($return_product_check->condition->value){
                ReturnProductCondition::GOOD->value => $return_product->product->stock_loan_return(),
                ReturnProductCondition::LOST->value => $return_product->product->stock_lost(),
                ReturnProductCondition::DAMAGED->value => $return_product->product->stock_damaged(),
                default => flashMessage('Kondisi barang tidak sesuai', 'error'),
            };

            $isOnTime = $return_product->isOnTime();
            $daysLate = $return_product->getDaysLate();
            $fineData = $this->calculateFine($return_product, $return_product_check, FineSetting::first(), $daysLate);

            DB::commit();

            if($isOnTime){
                if($fineData){
                    flashMessage($fineData['message'], 'error');
                    return to_route('admin.fines.create', $return_product->return_product_code);
                }

                flashMessage('Berhasil mengembalikan barang');
                return to_route('admin.return-products.index');
            } else{
                if($fineData){
                    flashMessage($fineData['message'], 'error');
                    return to_route('admin.fines.create', $return_product->return_product_code);
                }
            }


            flashMessage('Berhasil mengembalikan barang');
            return to_route('admin.return-products.index');
        } catch (Throwable $err) {
            DB::rollBack();
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()));
            return to_route('admin.loans.index');
        }
    }

    private function createFine(ReturnProduct $returnProduct, float $lateFee, float $otherFee): Fine
    {
        return $returnProduct->fine()->create([
            'user_id' => $returnProduct->user_id,
            'late_fee' => $lateFee,
            'other_fee' => $otherFee,
            'total_fee' => $lateFee + $otherFee,
            'fine_date' => Carbon::today(),
        ]);
    }

    private function calculateFine(ReturnProduct $returnProduct, ReturnProductCheck $returnProductCheck, FineSetting $fineSetting, int $daysLate): ?array
    {
        $late_fee = $fineSetting->late_fee_per_day * $daysLate;

        switch($returnProductCheck->condition->value){
            case ReturnProductCondition::DAMAGED->value:
                $other_fee = ($fineSetting->damage_fee_percentage / 100) * $returnProduct->product->price;
                $returnProduct->update([
                    'status' => ReturnProductStatus::FINE->value,
                ]);
                $this->createFine($returnProduct, $late_fee, $other_fee);

                return [
                    'message' => 'Kondisi barang rusak, harus membayar denda kerusakan',
                ];
            case ReturnProductCondition::LOST->value:
                $other_fee = ($fineSetting->lost_fee_percentage / 100)* 2 * $returnProduct->product->price;
                $returnProduct->update([
                    'status' => ReturnProductStatus::FINE->value,
                ]);
                $this->createFine($returnProduct, $late_fee, $other_fee);

                return [
                    'message' => 'Kondisi barang hilang, harus membayar denda kehilangan',
                ];
            default:
                if($daysLate > 0){
                    $returnProduct->update([
                        'status' => ReturnProductStatus::FINE->value,
                    ]);
                    $this->createFine($returnProduct, $late_fee, 0);
                    return [
                        'message' => 'Terlambat mengembalikan barang dan harus membayar denda keterlambatan',
                    ];
                } else {
                    $returnProduct->update([
                        'status' => ReturnProductStatus::RETURNED->value,
                    ]);

                    return null;
                }
        }
    }

    public function approve(ReturnProduct $returnProduct, ReturnProductRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $return_product_check = $returnProduct->returnProductCheck()->create([
                'condition' => $request->condition,
                'notes' => $request->notes,
            ]);

            match($return_product_check->condition->value){
                ReturnProductCondition::GOOD->value => $returnProduct->product->stock_loan_return(),
                ReturnProductCondition::LOST->value => $returnProduct->product->stock_lost(),
                ReturnProductCondition::DAMAGED->value => $returnProduct->product->stock_damaged(),
                default => flashMessage('Kondisi barang tidak sesuai', 'error'),
            };

            $isOnTime = $returnProduct->isOnTime();
            $dayslate = $returnProduct->getDaysLate();

            $fineData = $this->calculateFine($returnProduct, $return_product_check, FineSetting::first(), $dayslate);

            DB::commit();

            if ($isOnTime) {
                if ($fineData) {
                    flashMessage($fineData['message'], 'error');
                    return to_route('admin.return-products.index');
                }

                flashMessage('Berhasil menyetujui pengembalian barang');
                return to_route('admin.return-products.index');
            } else {
                if ($fineData) {
                    flashMessage($fineData['message'], 'error');
                    return to_route('admin.return-products.index');
                }
                return to_route('admin.return-products.index');
            }

        } catch(Throwable $err) {
            DB::rollBack();
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()));
            return to_route('admin.loans.index');
        }
    }
}
