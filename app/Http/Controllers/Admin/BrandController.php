<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Hasfile;
use Inertia\Response;
use App\Models\Brands;
use App\Enums\MessageType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\BrandRequest;
use App\Http\Resources\Admin\BrandResource;

class BrandController extends Controller
{
    use Hasfile;

    public function index(): Response
    {
        $brands = Brands::query()
            ->select(['id', 'name', 'slug', 'logo'])
            ->filter(request()->only(['search']))
            ->sorting(request()->only(['field', 'direction']))
            ->latest('created_at')
            ->paginate(request()->load ?? 10)
            ->withQueryString();

        return inertia('Admin/Brands/Index', [
            'page_settings' => [
                'title' => 'Penerbit',
                'subtitle' => 'Menampilkan semua data penerbit yang tersedia pada platform ini',
            ],
            'brands' => BrandResource::collection($brands)->additional([
                'meta' => [
                    'has_pages' => $brands->hasPages(),
                ],
            ]),
            'state' => [
                'page' => request()-> page ?? 1,
                'search' => request()-> search ?? '',
                'load' => 10,
            ],
        ]);
    }

    public function create(): Response
    {
        return inertia('Admin/Brands/Create', [
            'page_settings' => [
                'title' => 'Tambah Penerbit',
                'subtitle' => 'Buat penerbut baru disini. Klik simpan setelah selesai',
                'method' => 'POST',
                'action' => route('admin.brands.store'),
            ],
        ]);

    }

    public function store(BrandRequest $request): RedirectResponse
    {
        try {
            Brand::create([
                'name' => $name = $request->name,
                'slug' => str()->lower(str()->slug($name). str()->random(4)),
                'logo' => $this->upload_file($request, 'logo', 'brands')
            ]);

            flashMessage(MessageType::CREATED->message('Penerbit'));
            return to_route('admin.brands.index');
        } catch(Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.brands.index');
        }
    }

    public function edit(Brands $brands): Response
    {
        return inertia('Admin/Brands/Edit', [
            'page_settings' => [
                'title' => 'Edit Penerbit',
                'subtitle' => 'Edit penerbit disini. Klik simpan setelah selesai',
                'method' => 'PUT',
                'action' => route('admin.brands.update', $brands)
            ],
            'brands' => $brands,
        ]);
    }

    public function update(Brands $brands, BrandRequest $request): RedirectResponse
    {
        try {
            $brands->update([
                'name' => $name = $request->name,
                'slug' => $name !== $brands->name ? str()->lower(str()->slug($name) . str()->random(4)) : $brands->slug,
                'logo' => $this->update_file($request, $brands, 'logo', 'brands')
            ]);

            flashMessage(MessageType::UPDATED->message('Brand'));
            return to_route('admin.brands.index');
        } catch(Throwable $err) {
            flashMessage(MessageType::ERROR->message(error: $err->getMessage()), 'error');
            return to_route('admin.brands.index');
        }
    }
}
