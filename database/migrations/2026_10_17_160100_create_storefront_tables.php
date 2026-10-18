<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Top offer bar ("BUY 1 GET 1 FREE | SITE WIDE" …)
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->string('link')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Home page image slider
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('cta_text')->default('Shop Now');
            $table->string('cta_link')->default('/collections/all');
            $table->string('image')->nullable();
            $table->string('mobile_image')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('theme', 20)->default('maroon');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Home page blocks: category circles, "Handpicked For You" tiles and "Shop The Look" cards.
        Schema::create('home_items', function (Blueprint $table) {
            $table->id();
            $table->enum('section', ['circles', 'handpicked', 'look'])->index();
            $table->string('label')->nullable();
            $table->string('link')->nullable();
            $table->string('line1')->nullable();
            $table->string('line2')->nullable();
            $table->boolean('italic_first')->default(false);
            $table->string('bg', 20)->default('blush');
            $table->string('image')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('body');
            $table->boolean('show_in_footer')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('home_items');
        Schema::dropIfExists('hero_slides');
        Schema::dropIfExists('announcements');
    }
};
