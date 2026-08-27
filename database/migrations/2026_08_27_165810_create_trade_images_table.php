<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('trade_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');

            $table->enum('type', [
                'before',
                'after',
                'other',
            ])->default('before');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_images');
    }
};
