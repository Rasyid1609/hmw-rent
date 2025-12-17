<?php

namespace App\Models;

use App\Models\Loans;
use App\Models\Brands;
use App\Models\Stocks;
use App\Models\Category;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'prod_code',
        'title',
        'slug',
        'description',
        'release_year',
        'price_prod',
        'price',
        'cover',
        'category_id',
        'brand_id',
    ];

    protected function casts(): array{
        return [
            'status' => ProductStatus::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stocks::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loans::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(Brands::class);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function($query, $search) {
            $query->where(function($query) use($search){
                $query->whereAny([
                    'prod_code',
                    'title',
                    'slug',
                    'description',
                    'release_year',
                    'price',
                    'cover',
                    'category_id',
                    'brand_id',
                ], 'REGEXP', $search);
            });
        });
    }

    public function scopeSorting(Builder $query, array $sorts): void
    {
        $query->when($sorts['field'] ?? null && $sorts['direction'] ?? null, function($query) use($sorts){
            $query->orderBy($sorts['field'], $sorts['direction']);
        });
    }

    public function updateStock($colomnToDecrement, $colomnToIncrement)
    {
        if ($this->stock->$colomnToDecrement > 0) {
            return $this->stock()->update([
                $colomnToDecrement => $this->stock->$colomnToDecrement - 1,
                $colomnToIncrement => $this->stock->$colomnToIncrement + 1,
            ]);
        }

        return false;
    }

    public function stock_loan() {
        return $this->updateStock('available', 'loan');
    }

    public function stock_lost() {
        return $this->updateStock('loan', 'lost');
    }

    public function stock_damaged() {
        return $this->updateStock('loan', 'damaged');
    }

    public function stock_loan_return(){
        return $this->updateStock('loan', 'available');
    }

    public static function leastLoanProduct($limit = 5){
        return self::query()
            ->select(['id', 'title'])
            ->withCount('loans')
            ->orderBy('loans_count')
            ->limit($limit)
            ->get();
    }

    public static function mostLoanProduct($limit = 5){
        return self::query()
            ->select(['id', 'title'])
            ->withCount('loans')
            ->orderByDesc('loans_count')
            ->limit($limit)
            ->get();
    }
}
