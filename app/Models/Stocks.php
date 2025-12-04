<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stocks extends Model
{
    protected $fillable = [
        'product_id',
        'total',
        'available',
        'loan',
        'lost',
        'damaged',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query ->when($filters['search'] ?? null, function($query, $search){
            $query->where(function($query) use($search){
                $query->whereHas('product', fn($query) => $query->where('title', 'REGEXP', $search));
            });
        });
    }

    public function scopeSorting(Builder $query, array $sorts): void
    {
        $query->when($sorts['field'] ?? null && $sorts['direction'] ?? null, function($query) use($sorts){
            match($sorts['field']){
                'product_id' => $query->join('products', 'stocks.product_id', '=', 'products.id')
                    ->orderBy('products.title', $sorts['direction']),
                default => $query->orderBy($sorts['field'], $sorts['direction']),
            };
        });
    }
}
