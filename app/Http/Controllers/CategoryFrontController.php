<?php

namespace App\Http\Controllers;

use Inertia\Response;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\ProductFrontResource;
use App\Http\Resources\CategoryFrontResource;

class CategoryFrontController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'cover', 'created_at'])
            ->latest()
            ->paginate(8);

        return inertia('Front/Categories/Index', [
            'page_settings' => [
                'title' => 'Kategori',
                'subtitle' => 'Menampilkan semua kategori yang tersedia pada plathform ini.',
            ],
            'categories' => CategoryFrontResource::collection($categories)->additional([
                'meta' => [
                    'has_pages' => $categories->hasPages(),
                ],
            ]),
        ]);
    }

    public function show(Category $category): Response
    {
        $products = Product::query()
            ->select(['id', 'title', 'slug', 'status', 'cover', 'description', 'category_id'])
            ->where('category_id', $category->id)
            ->paginate(12);

        return inertia('Front/Categories/Show', [
            'page_settings' => [
                'title' => $category->name,
                'subtitle' => "Menampilkan semua barang yang tersedia pada kategori {$category->name} pada platform ini",
            ],
            'products' => ProductFrontResource::collection($products)->additional([
                'meta' => [
                    'has_pages' => $products->hasPages(),
                ],
            ]),
        ]);
    }
}
