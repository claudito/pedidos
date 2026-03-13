<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status');
            $table->text('description')->nullable();
            $table->dateTime('tracked_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};
