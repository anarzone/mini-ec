<?php

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
            $table->string('order_number');
            $table->string('contract_email');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 40)->nullable();
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->foreign('billing_address_id')->references('id')->on('addresses');
            $table->foreign('shipping_address_id')->references('id')->on('addresses');
            $table->timestamps();
            $table->softDeletes();
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
