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
        // Tabel pivot untuk melacak kepemilikan game oleh user (Library)
        Schema::create('game_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Kolom tambahan untuk menyimpan data pembelian
            $table->decimal('purchase_price', 10, 2); // Harga saat dibeli (setelah diskon)
            $table->string('transaction_id')->unique(); // ID unik transaksi
            
            // PERBAIKAN: Hapus ->after('transaction_id') karena error di MySQL saat create table
            $table->string('purchase_method')->nullable(); 
            
            // Pastikan tidak ada duplikasi game untuk user yang sama
            $table->unique(['user_id', 'game_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_user');
    }
};