<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\User;
use App\Models\Loans;
use Inertia\Response;
use App\Models\Product;
use App\Enums\MessageType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\LoanRequest;
use App\Http\Resources\Admin\LoanResource;

class LoanController extends Controller
{
    public function index(): Response
    {
        $loans = Loans::query()
            ->select(['id', 'loan_code', 'user_id', 'product_id', 'loan_date', 'due_date', 'created_at'])
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->with(['product', 'user', 'returnProduct'])
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('Admin/Loans/Index', [
            'page_settings' => [
                'title' => 'Peminjaman',
                'subtitle' => 'Menampilkan semua data peminjaman yang tersedia pada platform ini.'
            ],
            'loans' => LoanResource::collection($loans)->additional([
                'meta' => [
                    'has_pages' => $loans->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load' => 10
            ],
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Loans/Create', [
            'page_settings' => [
                'title' => 'Tambah Peminjaman',
                'subtitle' => 'Buat peminjaman buku di sini. Klik simpan setelah selesai. ',
                'method' => 'POST',
                'action' => route('admin.loans.store'),
            ],
            'page_data' => [
                'date' => [
                    'loan_date' => Carbon::now()->toDateString(),
                    'due_date' => Carbon::now()->addDays(7)->toDateString(),
                ],
                'products' => Product::query()
                    ->select(['id', 'title'])
                    ->whereHas('stock', fn($query) => $query->where('available', '>', 0))
                    ->get()
                    ->map(fn($item) => [
                        'value' => $item->title,
                        'label' => $item->title
                    ]),
                'users' => User::query()
                    ->select(['id', 'name'])
                    ->whereHas('roles', fn($query) => $query->where('name', 'member'))
                    ->get()
                    ->map(fn($item) => [
                        'value' => $item->name,
                        'label' =>$item->name,
                    ]),
            ]
        ]);
    }

    public function store(LoanRequest $request): RedirectResponse
    {
        try {
            $product = Product::query()
                ->where('title', $request->product)
                ->firstOrFail();

            $user = User::query()
                ->where('name', $request->user)
                ->firstOrFail();

            if (Loans::checkLoanProduct($user->id, $product->id)) {
                flashMessage('Pengguna sudah meminjam product ini', 'error');
                return to_route('admin.loans.index');
            }

            $product->stock->available > 0
            ? tap(Loans::create([
                'loan_code' => str()->lower(str()->random(10)),
                'user_id' => $user->id,
                'product_id' => $product->id,
                'loan_date' => Carbon::now()->toDateString(),
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
            ]), function($loan){
                $loan->product->stock_loan();
                flashMessage('Berhasil menambahkan peminjaman');
            })
            : flashMessage('Stok buku tidak tersedia', 'error');

            return to_route('admin.loans.index');
        } catch (Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.loans.index');
        }
    }

    public function edit(Loans $loan): Response
    {
        return inertia('Admin/Loans/Edit', [
            'page_settings' => [
                'title' => 'Edit Peminjaman',
                'subtitle' => 'Edit peminjaman buku di sini. Klik simpan setelah selesai. ',
                'method' => 'PUT',
                'action' => route('admin.loans.update', $loan),
            ],
            'page_data' => [
                'date' => [
                    'loan_date' => Carbon::now()->toDateString(),
                    'due_date' => Carbon::now()->addDays(7)->toDateString(),
                ],
                'products' => Product::query()
                    ->select(['id', 'title'])
                    ->whereHas('stock', fn($query) => $query->where('available', '>', 0))
                    ->get()
                    ->map(fn($item) => [
                        'value' => $item->title,
                        'label' => $item->title
                    ]),
                'users' => User::query()
                    ->select(['id', 'name'])
                    ->whereHas('roles', fn($query) => $query->where('name', 'member'))
                    ->get()
                    ->map(fn($item) => [
                        'value' => $item->name,
                        'label' =>$item->name,
                    ]),
                'loan' => $loan->load(['user', 'product']),
            ]
        ]);
    }

    public function update(Loan $loan, LoanRequest $request): RedirectResponse
    {
        try {
            $product = Product::query()
                ->where('title', $request->product)
                ->firstOrFail();

            $user = User::query()
                ->where('name', $request->user)
                ->firstOrFail();

            if (Loan::checkLoanProduct($user->id, $product->id)) {
                flashMessage('Pengguna sudah meminjam buku ini', 'error');
                return to_route('admin.loans.index');
            }

            $loan->update([
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            flashMessage(MessageType::UPDATED->message('Peminjaman'));
            return to_route('admin.loans.index');
        } catch (Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.loans.index');
        }
    }

    public function destroy(Loan $loan): RedirectResponse
    {
        try {
            $loan->delete();

            flashMessage(MessageType::DELETED->message('Peminjaman'));
            return to_route('admin.loans.index');
        } catch (Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.loans.index');
        }
    }
}
