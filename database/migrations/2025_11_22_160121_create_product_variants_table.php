<?php

use App\Models\Product;
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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 64);
            $table->bigInteger('price_cents');
            $table->char('currency', 3);
            $table->integer('weight_g')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(Product::class);
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
