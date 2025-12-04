<?php

use App\Enums\ProductStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('prod_code');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('description');
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('price_prod')->default(0);
            $table->string('status')->default(ProductStatus::AVAILABLE->value);
            $table->string('cover')->nullable();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
