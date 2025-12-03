<?php

namespace App\Http\Controllers\Admin;

use inertia;
use App\Models\Loans;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\LoanStatisticResource;

class LoanStatisticController extends Controller
{
    public function index(): Response
    {
        return inertia('Admin/LoanStatistics/Index', [
            'page_settings' => [
                'title' => 'Statistik Peminjaman',
                'subtitle' => 'Menampilkan statistik peminjaman yang tersedia pada platform ini.'
            ],
            'page_data' => [
                'least_loan_products' => LoanStatisticResource::collection(Product::leastLoanProducts(5)),
                'most_loan_products' => LoanStatisticResource::collection(Product::mostLoanProducts(5)),
                 'total_loan' => Loans::totalLoanProducts(),
            ],
        ]);
    }
}
