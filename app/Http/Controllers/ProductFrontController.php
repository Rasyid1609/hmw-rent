<?php

namespace App\Http\Controllers;

use Inertia\Response;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryFrontResource;
use App\Http\Resources\ProductFrontSingleResource;

class ProductFrontController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'cover', 'created_at'])
            ->whereHas('products')
            ->with([
                'products' => fn($query) => $query->limit(4),
            ])
            ->latest('created_at')
            ->get();

        return inertia('Front/Products/Index', [
            'page_settings' => [
                'title' => 'Barang',
                'subtitle' => 'Menampilkan semua barang yang tersedia pada platform ini.',
            ],
            'categories' => CategoryFrontResource::collection($categories),
        ]);
    }

    public function show(Product $product): Response
    {
        return inertia('Front/Products/Show', [
            'page_settings' => [
                'title' => $product->title,
                'subtitle' => "Menampilkan detail informasi barang {$product->title}",
            ],
            'product' => new ProductFrontSingleResource($product->load(['category', 'publisher', 'stock'])),
        ]);
    }
}
