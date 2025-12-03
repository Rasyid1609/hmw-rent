<?php

namespace App\Http\Controllers;

use inertia;
use App\Models\User;
use App\Models\Loans;
use Inertia\Response;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ReturnProduct;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\TransactionLoanResource;
use App\Http\Resources\Admin\TransactionReturnProductResource;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $loans = Loans::query()
            ->select(['id', 'loan_code', 'product_id', 'user_id', 'created_at'])
            ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                return $query;
            }, function($query){
                return $query->where('user_id', auth()->user()->id);
            })
            ->latest('created_at')
            ->limit(5)
            ->with(['user', 'product'])
            ->get();

            $return_products = ReturnProduct::query()
                ->select(['id', 'return_product_code', 'product_id', 'user_id', 'created_at'])
                ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                    return $query;
                }, function($query){
                    return $query->where('user_id', auth()->user()->id);
                })
                ->latest('created_at')
                ->limit(5)
                ->with('user', 'product')
                ->get();


        return inertia('Dashboard', [
            'auth' => [
                'user' => [
                    'id' => auth()->id(),
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'role' => auth()->user()->getRoleNames(),
                    'permissions' => auth()->user()->getPermissionNames(),
                ]
            ],
            'page_settings' => [
                'title' => 'Dashboard',
                'subtitle' => 'Menampilkan semua statistik pada platform ini.',
            ],
            'page_data' => [
                'transactionChart' => $this->chart(),
                'loans' => TransactionLoanResource::collection($loans),
                'return_products' => TransactionReturnProductResource::collection($return_products),
                'total_products' => auth()->user()->hasAnyRole(['admin', 'operator']) ? Product::count() : 0,
                'total_users' => auth()->user()->hasAnyRole(['admin', 'operator']) ? User::count() : 0,
                'total_loans' => Loans::query()
                    ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                        return $query;
                    }, function($query){
                        return $query->where('user_id', auth()->user()->id);
                    })->count(),
                'total_returns' => ReturnProduct::query()
                    ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                        return $query;
                    }, function($query){
                        return $query->where('user_id', auth()->user()->id);
                    })->count(),
                'total_fines' => auth()->user()->hasRole('member') ? Fine::query()
                    ->where('user_id', auth()->user()->id)
                    ->sum('total_fee') : 0,
                ],
        ]);
    }

    public function chart(): array
    {
        $end_date = Carbon::now();

        $start_date = $end_date->copy()->subMonth()->startOfMonth();

        $loans = Loans::query()
            ->selectRaw('DATE(loan_date) as date, COUNT(*) as loan')
            ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                return $query;
            }, function($query) {
                return $query->where('user_id', auth()->user()->id);
            })
            ->whereBetween('loan_date', [$start_date, $end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('loan', 'date');

        $return_products = ReturnProduct::query()
            ->selectRaw('DATE(return_date) as date, COUNT(*) as returns')
            ->when(auth()->user()->hasAnyRole(['admin', 'operator']), function($query){
                return $query;
            }, function($query) {
                return $query->where('user_id', auth()->user()->id);
            })
            ->whereNotNull('return_date')
            ->whereBetween('return_date', [$start_date, $end_date])
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('returns', 'date');

        $charts = [];
        $period = Carbon::parse($start_date)->daysUntil($end_date);

        foreach($period as $date){
            $date_string = $date->toDateString();
            $charts[] = [
                'date' => $date_string,
                'loan' => $loans->get($date_string, 0),
                'return_product' => $return_products->get($date_string, 0)
            ];
        }
        // dd($charts);

        return $charts;
    }
}
