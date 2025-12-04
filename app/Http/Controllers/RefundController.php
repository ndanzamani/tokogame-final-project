<?php

namespace App\Http\Controllers;

use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Game; // Pastikan model Game di-import

class RefundController extends Controller
{
    public function create()
    {
        // PERBAIKAN: Ambil game yang dimiliki user (yang sudah dibeli)
        $user = Auth::user();
        // Menggunakan relasi games() yang baru di User model
        $games = $user->games()
                      // Hanya tampilkan game yang belum diajukan refund (pending)
                      ->whereDoesntHave('refundRequests', function($query) use ($user) {
                          $query->where('user_id', $user->id)
                                ->where('status', 'pending');
                      })
                      ->get();

        // Ambil riwayat refund user untuk ditampilkan sebagai informasi
        $history = RefundRequest::where('user_id', $user->id)
                                ->with('game')
                                ->orderBy('created_at', 'desc')
                                ->get();
                                
        return view('pages.refunds', compact('games', 'history'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'reason' => 'required|string|min:10',
        ]);

        $user = Auth::user();

        // Cek apakah user benar-benar memiliki game tersebut (Validasi kepemilikan)
        // Cek melalui tabel pivot game_user
        if (!$user->games()->where('game_id', $request->game_id)->exists()) {
            return back()->with('error', 'Anda tidak memiliki game ini.');
        }

        // Cek apakah sudah ada request pending untuk game yang sama
        $existing = RefundRequest::where('user_id', $user->id)
            ->where('game_id', $request->game_id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'Request refund untuk game ini sedang diproses.');
        }

        RefundRequest::create([
            'user_id' => $user->id,
            'game_id' => $request->game_id,
            'reason' => $request->reason,
            'status' => 'pending', // Pastikan status default
        ]);

        return back()->with('success', 'Permintaan refund berhasil dikirim dan menunggu persetujuan Admin.');
    }
}