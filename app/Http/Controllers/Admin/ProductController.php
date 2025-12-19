<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Hasfile;
use Inertia\Response;
use App\Models\Brands;
use App\Models\Product;
use App\Models\Category;
use App\Enums\MessageType;
use App\Enums\ProductStatus;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Resources\Admin\ProductResource;

class ProductController extends Controller
{
    use Hasfile;

    public function index(): Response
    {
        $products = Product::query()
        ->select(['id', 'prod_code', 'title', 'description', 'release_year', 'slug', 'status', 'price', 'category_id', 'brand_id', 'created_at'])
        ->filter(request()->only(['search']))
        ->sorting(request()->only(['field', 'direction']))
        ->with(['category','stock','brand'])
        ->latest('created_at')
        ->paginate(request()->load ?? 10)
        ->withQueryString();

        return inertia('Admin/Products/Index', [
            'page_settings' => [
                'title' => 'Barang',
                'subtitle' => 'Menampilkan semua data barang yang tersedia pada platform ini',
            ],
            'products' => ProductResource::collection($products)->additional([
                'meta' => [
                    'has_pages' => $products->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()->page ?? 1,
                'search' => request()->search ?? '',
                'load' => 10,
            ],
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Products/Create', [
            'page_settings' => [
                'title' => 'Tambah Barang',
                'subtitle' => 'Tambah barang baru disini. Klik simpan setelah selesai.',
                'method' => 'POST',
                'action' => route('admin.products.store'),
            ],
            'page_data' => [
                'categories' => Category::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
                'brands' => Brands::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
            ]
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            Product::create([
                'prod_code' => $this->productCode(),
                'title' => $title = $request->title,
                'slug' => str()->lower(str()->slug($title). str()->random(4)),
                'description' => $request->description,
                'release_year' => $request->release_year,
                'status' => $request->total > 0 ? ProductStatus::AVAILABLE->value : ProductStatus::UNAVAILABLE->value,
                'cover' => $this->upload_file($request, 'cover', 'products'),
                'price' => $request->price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
            ]);

            flashMessage(MessageType::CREATED->message('Barang'));
            return to_route('admin.products.index');
        } catch(Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.products.index');
        }

    }

    public function edit(Product $product): Response
    {
        return inertia('Admin/Products/Edit', [
            'page_settings' => [
                'title' => 'Edit Barang',
                'subtitle' => 'Edit barang baru disini. Klik simpan setelah selesai.',
                'method' => 'PUT',
                'action' => route('admin.products.update', $product),
            ],
            'product' =>$product,

            'page_data' => [
                'categories' => Category::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
                'brands' => Brands::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
            ]
        ]);
    }

    public function update(Product $product, ProductRequest $request): RedirectResponse
    {
        try {
            $product->update([
                'prod_code' => $this->productCode(),
                'title' => $title = $request->title,
                'slug' => $title !== $product->title ?  str()->lower(str()->slug($title). str()->random(4)) : $product->slug,
                'status' => $request->total > 0 ? ProductStatus::AVAILABLE->value : ProductStatus::UNAVAILABLE->value,
                'cover' => $this->update_file($request, $product, 'cover', 'products'),
                'description' => $request->description,
                'release_year' => $request->release_year,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
            ]);

            flashMessage(MessageType::UPDATED->message('Barang'));
            return to_route('admin.products.index');
        } catch(Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.products.index');
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $this->delete_file($product, 'cover');
            $product->delete();

            flashMessage(MessageType::DELETED->message('Barang'));
            return to_route('admin.products.index');
        } catch (Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.products.index');
        }
    }

    private function productCode(): string
    {
        $prefix = 'PRD-';

        $lastProduct = Product::query()
            ->where('prod_code', 'like', $prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(prod_code, -4) AS UNSIGNED) DESC')
            ->first();

        $order = 1;

        if ($lastProduct) {
            $lastOrder = (int) substr($lastProduct->prod_code, -4);
            $order = $lastOrder + 1;
        }

        return $prefix . str_pad($order, 4, '0', STR_PAD_LEFT);
    }
}
