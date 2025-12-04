<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->string('genre');
            $table->string('publisher');
            $table->date('release_date');
            $table->string('cover_image');
            
            // --- KOLOM YANG HILANG/ERROR DITAMBAHKAN DI SINI ---
            // Menggunakan tipe JSON untuk menyimpan array path screenshots
            $table->json('screenshots')->nullable(); 
            $table->string('trailer_url')->nullable();
            // --- END KOLOM HILANG ---
            
            $table->boolean('is_featured')->default(false);
            
            // Kolom is_approved, role, dkk akan ditambahkan di migration 2025_12_04_005000
            // Jadi kita tidak perlu khawatir soal itu di sini.

            $table->integer('discount_percent')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};