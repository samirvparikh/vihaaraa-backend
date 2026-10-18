<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('blurb')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Collections are the storefront groupings: New Launches, Best Sellers, Demi-Fine, Ethnic, Western, 925 Silver…
        // `rule` lets a collection fill itself automatically (all / new / bestseller); otherwise products are attached manually.
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('handle')->unique();
            $table->string('blurb')->nullable();
            $table->string('image')->nullable();
            $table->enum('rule', ['manual', 'all', 'new', 'bestseller'])->default('manual');
            $table->enum('menu_group', ['style', 'finish'])->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 40)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('base_metal')->nullable();
            $table->string('plating')->nullable();
            $table->json('stones')->nullable();
            $table->json('sizes')->nullable();
            $table->string('weight', 40)->nullable();
            $table->string('includes')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('mrp', 10, 2);
            $table->unsignedInteger('stock')->default(100);
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            // Illustration fallback used by the storefront when no photo is uploaded.
            $table->string('shape', 20)->default('necklace');
            $table->string('stone', 20)->default('none');
            $table->string('bg', 20)->default('blush');
            $table->string('image')->nullable();
            $table->boolean('is_new')->default(false)->index();
            $table->boolean('is_bestseller')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('collection_product', function (Blueprint $table) {
            $table->foreignId('collection_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['collection_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_product');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('collections');
        Schema::dropIfExists('categories');
    }
};
