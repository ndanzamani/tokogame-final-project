<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopupController extends Controller
{
    // Menampilkan halaman topup
    public function create()
    {
        // Pilihan jumlah top-up Kukus Money (IDR)
        $amounts = [
            50000, 100000, 200000, 500000, 1000000,
        ];

        return view('pages.topup', compact('amounts'));
    }

    // Memproses topup Kukus Money (Simulasi)
    public function store(Request $request)
    {
        $request->validate([
            // Validasi: pastikan amount adalah angka, minimal 10 ribu (simulasi)
            'amount' => 'required|numeric|min:10000|max:10000000', 
        ]);

        $user = Auth::user();
        $topupAmount = $request->amount;

        // Simulasi proses pembayaran sukses, tambahkan saldo
        $user->kukus_money_balance += $topupAmount;
        $user->save();

        return redirect()->back()->with('success', 
            "Topup sebesar Rp" . number_format($topupAmount, 0, ',', '.') . 
            " berhasil! Saldo Kukus Money Anda sekarang: Rp" . number_format($user->kukus_money_balance, 0, ',', '.')
        );
    }
}