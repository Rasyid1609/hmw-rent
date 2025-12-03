<?php

namespace App\Http\Controllers\Admin;

use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\ReturnProduct;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\ReturnFineSingleResource;

class FineController extends Controller
{
    public function create(ReturnProduct $returnProduct) : Response
    {
        return inertia('Admin/Fines/Create', [
            'page_settings' => [
                'title' => 'Denda',
                'subtitle' => 'Selesaikan pembayaran denda terlebih dahulu.'
            ],
            'return_product' => new ReturnFineSingleResource($returnBook->load([
                'product',
                'fine',
                'loan',
                'user',
                'returnProductCheck'
            ])),
        ]);
    }
}
