<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\Product;
use App\Models\Category;
use App\Enums\MessageType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    use Hasfile;

    public function index(): Response
    {
        $products = Product::query()
        ->select(['id', 'product_code', 'title', 'slug', 'status', 'price', 'category_id', 'publisher_id', 'created_at'])
        ->filter(request()->only(['search']))
        ->sorting(request()->only(['field', 'direction']))
        ->with(['category','stock','brand'])
        ->latest('created_at')
        ->paginate(request()->load ?? 10)
        ->withQueryString();

        return inertia('Admin/Products/Index', [
            'page_settings' => [
                'title' => 'Buku',
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
                'publicationYears' => range(2000, now()->year),
                'languages' => ProductLanguage::options(),
                'categories' => Category::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
                'publishers' => Publisher::query()->select(['id', 'name'])->get()->map(fn($item) => [
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
                'product_code' => $this->productCode(
                    $request->publication_year,
                    $request->category_id
                ),
                'title' => $title = $request->title,
                'slug' => str()->lower(str()->slug($title). str()->random(4)),

                'synopsis' => $request->synopsis,
                'number_of_pages' => $request->number_of_pages,
                'status' => $request->total > 0 ? ProductStatus::AVAILABLE->value : ProductStatus::UNAVAILABLE->value,
                'cover' => $this->upload_file($request, 'cover', 'products'),
                'price' => $request->price,
                'category_id' => $request->category_id,
                'publisher_id' => $request->publisher_id,
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
                'publicationYears' => range(2000, now()->year),
                'languages' => ProductLanguage::options(),
                'categories' => Category::query()->select(['id', 'name'])->get()->map(fn($item) => [
                    'value' => $item->id,
                    'label' => $item->name,
                ]),
                'publishers' => Publisher::query()->select(['id', 'name'])->get()->map(fn($item) => [
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
                'product_code' => $this->productCode(
                    $request->category_id
                ),
                'title' => $title = $request->title,
                'slug' => $title !== $product->title ?  str()->lower(str()->slug($title). str()->random(4)) : $product->slug,
                'status' => $request->total > 0 ? ProductStatus::AVAILABLE->value : ProductStatus::UNAVAILABLE->value,
                'cover' => $this->update_file($request, $product, 'cover', 'products'),
                'price' => $request->price,
                'category_id' => $request->category_id,
                'publisher_id' => $request->publisher_id,
            ]);

            flashMessage(MessageType::UPDATED->message('Buku'));
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

            flashMessage(MessageType::DELETED->message('Buku'));
            return to_route('admin.products.index');
        } catch (Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.products.index');
        }
    }

    private function productCode(int $publication_year, int $category_id): string
    {
        $category = Category::find($category_id);
        $product_code_prefix = 'CA'. $publication_year. '.' . str()->slug($category->name). '.';

        $last_product = Product::query()
            ->where('product_code', 'like', $product_code_prefix . '%')
            ->orderByRaw('CAST(SUBSTRING(product_code, -4) AS UNSIGNED) DESC')
            ->first();

        $order = 1;

        if ($last_product) {
            $last_order = (int) substr($last_product->product_code, -4);
            $order = $last_order + 1;
        }

        $ordering = str_pad($order, 4, '0', STR_PAD_LEFT);
        return $product_code_prefix . $ordering;
    }
}
