<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand');
            $table->string('model');
            $table->year('year');
            $table->string('color');
            $table->string('plate_number')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('seats');
            $table->enum('transmission', ['auto', 'manual']);
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric']);
            $table->decimal('price_per_day', 10, 2);
            $table->integer('mileage')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};