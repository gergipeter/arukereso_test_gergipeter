<?php

use App\Models\Address;
use App\Models\Customer;
use App\Models\OrderStatus;
use App\Models\ShippingMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Customer::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(OrderStatus::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(ShippingMethod::class)->constrained()->restrictOnDelete();

            $table->foreignId('billing_address_id')->constrained('addresses')->restrictOnDelete();
            $table->foreignId('shipping_address_id')->constrained('addresses')->restrictOnDelete();
            
            $table->date('order_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
