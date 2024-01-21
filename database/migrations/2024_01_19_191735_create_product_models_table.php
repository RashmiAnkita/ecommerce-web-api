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
        Schema::create('product_models', function (Blueprint $table) {
            $table->id()->comment('Product ID');
            $table->string('product_name');
            $table->longText('product_description');
            $table->integer('product_category_id')->comment('Product Category ID from Product Category Table');
            $table->string('product_price');
            $table->decimal('product_discount_price', 10, 2)->nullable();
            $table->integer('product_quantity')->comment('No. of products');
            $table->string('product_images');
            $table->string('product_manufacturer');
            $table->enum('product_status', ['A', 'D'])->default('A')->comment('A=>Active, D => Deactive');
            $table->string('product_slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_models');
    }
};
