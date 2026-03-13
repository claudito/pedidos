<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique();
            $table->string('prefix', 20)->default('PED-');
            $table->unsignedBigInteger('current_number')->default(0);
            $table->unsignedInteger('padding')->default(3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};
