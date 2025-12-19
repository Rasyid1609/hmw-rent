<?php

namespace App\Models;

use App\Models\User;
use App\Models\Product;
use App\Models\ReturnProduct;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $fillable = [
        'loan_code',
        'user_id',
        'product_id',
        'loan_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function returnProduct(): HasOne
    {
        return $this->hasOne(ReturnProduct::class);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search){
            $query->where(function($query) use ($search){
                $query->whereAny([
                    'load_code',
                    'load_date',
                    'due_date',
                ], 'REGEXP', $search);
            });
        });
    }

    public function scopeSorting(Builder $query, array $sorts): void
    {
        $query->when($sorts['field'] ?? null && $sorts['direction'] ?? null, function($query) use ($sorts){
            $query->orderBy($sorts['field'], $sorts['direction']);
        });
    }

    public static function checkLoanProduct(int $user_id, int $product_id): bool
    {
        return self::query()
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->whereDoesntHave('returnProduct', fn($query) => $query->where('product_id', $product_id)->where('user_id', $user_id))
            ->exists();
    }

    public static function totalLoanProducts(): array
    {
        return [
            'days' => self::whereDate('created_at', Carbon::now()->toDateString())->count(),
            'weeks' => self::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'months' => self::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'years' => self::whereYear('created_at', Carbon::now()->year)->count(),
        ];
    }
}
