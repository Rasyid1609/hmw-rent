<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Loan;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ReturnProduct;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Http\Resources\ReturnProductFrontResource;
use App\Http\Resources\ReturnProductFrontSingleResource;

class ReturnProductFrontController extends Controller
{
    public static function middleware(): array
    {
        return [
            new Middleware('password.confirm', except: ['store']),
        ];
    }

    public function index(): Response
    {
        $return_products = ReturnProduct::query()
        ->select(['id', 'return_product_code', 'status', 'loan_id', 'user_id', 'product_id', 'return_date', 'created_at'])
        ->where('user_id', auth()->user()->id)
        ->filter(request()->only(['search']))
        ->sorting(request()->only(['field', 'direction']))
        ->with(['product', 'fine', 'loan', 'user', 'returnProductCheck'])
        ->latest('created_at')
        ->paginate(request()->load ?? 10)
        ->withQueryString();

        return inertia('Front/ReturnProducts/Index', [
            'page_settings' => [
                'title' => 'Pengembalian',
                'subtitle' => 'Menampilkan semua data pengembalian anda yang tersedia pada platform ini. '
            ],
            'return_products' => ReturnProductFrontResource::collection($return_products)->additional([
                'meta' => [
                    'has_pages' => $return_products->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load' => 10
            ],
            'page_data' => [
                'returned' => ReturnProduct::query()->member(auth()->user()->id)->returned()->count(),
                'fine' => ReturnProduct::query()->member(auth()->user()->id)->fine()->count(),
                'checked' => ReturnProduct::query()->member(auth()->user()->id)->checked()->count(),
            ]
        ]);
    }

    public function show(ReturnProduct $returnProduct): Response
    {
        return inertia('Front/ReturnProducts/Show', [
            'page_settings' => [
                'title' => 'Detail Pengembalian Barang',
                'subtitle' => 'Dapat melihat informasi tentang detail barang yang anda kembalikan',
            ],
            'return_product' => new ReturnProductFrontSingleResource(
                $returnProduct->load([
                    'product',
                    'user',
                    'loan',
                    'fine',
                    'returnProductCheck'
                ])
            ),
        ]);
    }

    public function store(Product $product, Loan $loan): RedirectResponse
    {
        $return_product = $loan->returnProduct()->create([
            'return_product_code' => str()->lower(str()->random(10)),
            'product_id' => $product->id,
            'user_id' => auth()->user()->id,
            'return_date' => Carbon::today(),
        ]);

        flashMessage('Barang anda sedang dilakukan pengecekan oleh petugas kami');
        return to_route('front.return-products.show', [$return_product->return_product_code]);
    }
}
