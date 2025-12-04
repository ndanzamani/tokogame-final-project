<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // Casting kolom screenshots agar otomatis jadi Array/JSON
    protected $casts = [
        'screenshots' => 'array',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'release_date' => 'date',
    ];

    // --- RELASI BARU: Permintaan Refund ---
    public function refundRequests()
    {
        return $this->hasMany(RefundRequest::class);
    }
    // --- END RELASI BARU ---
}