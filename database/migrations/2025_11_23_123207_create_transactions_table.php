<?php

use App\Enums\RentPaymentStatus;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trx_id');
            $table->string('phone_number');
            $table->string('address');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_amount');
            $table->unsignedBigInteger('duration');
            $table->string('payment_status')->default(RentPaymentStatus::PENDING->value);
            $table->enum('delivery_type', ['pickup', 'home_delivery'])->default('pickup');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
