<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('sets_preparation_date')->default(false);
            $table->boolean('sets_dispatch_date')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('order_status_id')->nullable()->after('status')->constrained('order_statuses')->nullOnDelete();
        });

        Schema::table('trackings', function (Blueprint $table) {
            $table->foreignId('order_status_id')->nullable()->after('order_id')->constrained('order_statuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trackings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_status_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_status_id');
        });

        Schema::dropIfExists('order_statuses');
    }
};
