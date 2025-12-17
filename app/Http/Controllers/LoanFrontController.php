<?php

namespace App\Http\Controllers;

use App\Models\Loans;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Http\Resources\LoanFrontResource;
use App\Http\Resources\LoanFrontSingleResource;

class LoanFrontController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('password.confirm', except: ['store']),
        ];
    }

    public function index(): Response
    {
        $loans = Loans::query()
            ->select(['id', 'loan_code', 'user_id', 'product_id', 'loan_date','due_date', 'created_at'])
            ->where('user_id', auth()->user()->id)
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->with(['product', 'user'])
            ->latest()
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('Front/Loans/Index', [
            'page_settings' => [
                'title' => 'Peminjaman',
                'subtitle' => 'Menampilkan semua data peminjaman yang tersedia pada platform ini.',
            ],
            'loans' => LoanFrontResource::collection($loans)->additional([
                'meta' => [
                    'has_pages' => $loans->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load' => 10,
            ],
        ]);
    }

    public function show(Loan $loan): Response
    {
        return inertia('Front/Loans/Show', [
            'page_settings' => [
                'title' => "Detail Peminjaman Barang",
                'subtitle' => "Dapat melihat informasi detail barang yang anda pinjam.",
            ],
            'loan' => new LoanFrontSingleResource($loan->load(['product', 'user', 'returnProduct'])),
        ]);
    }

    public function store(Product $product): RedirectResponse
    {
        if (Loans::checkLoanProduct(auth()->user()->id, $product->id)){
            flashMessage('Anda sudah meminjam barang ini, harap kembalikan barangnya terlebih dahuku', 'error');
            return  to_route('front.products.show', $product->slug);
        }

        if ($product->stock->available <= 0){
            flashMessage('Stok barang tidak tersedia.', 'error');
            return to_route('front.products.show');
        }

        $loan = tap(Loans::create([
            'loan_code' => str()->lower(str()->random(10)),
                'user_id' => auth()->user()->id,
                'product_id' => $product->id,
                'loan_date' => Carbon::now()->toDateString(),
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
        ]), function($loan){
            $loan->product->stock_loan();
            flashMessage('Berhasil melakukan peminjaman barang');
        });

        return to_route('front.loans.index');
    }
}
