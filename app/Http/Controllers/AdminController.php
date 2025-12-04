<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RefundRequest;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB facade

class AdminController extends Controller
{
    public function index()
    {
        // Data untuk Dashboard Utama (Yang Pending/Active saja)
        
        // 1. Publisher Requests (Pending)
        $publisherRequests = User::where('publisher_request_status', 'pending')->get();
        
        // 2. Refund Requests (Pending) - PASTIKAN AMBIL DATA HARGA DARI PIVOT
        // Kita perlu mencari harga pembelian di tabel pivot 'game_user' secara manual karena relasi refund->game tidak otomatis membawa pivot data user.
        $refunds = RefundRequest::where('status', 'pending')->with(['user', 'game'])->orderBy('created_at', 'desc')->get();

        // Tambahkan harga pembelian ke setiap request refund
        foreach ($refunds as $refund) {
             // Cari data pivot game_user berdasarkan user_id dan game_id
             // Gunakan DB untuk mengambil data mentah dari tabel pivot
             $purchaseData = DB::table('game_user')
                                 ->where('user_id', $refund->user_id)
                                 ->where('game_id', $refund->game_id)
                                 ->first();
             
             // Tambahkan harga pembelian ke objek refund
             $refund->purchase_price = $purchaseData ? $purchaseData->purchase_price : 0;
        }


        // 3. Game Approval Requests (Pending) - FITUR BARU
        $pendingGames = Game::where('is_approved', false)->orderBy('created_at', 'desc')->get();

        return view('admin.dashboard', compact('publisherRequests', 'refunds', 'pendingGames'));
    }

    // --- HALAMAN HISTORY BARU ---
    public function history()
    {
        // 1. Game yang Ditolak (Soft Deleted)
        $rejectedGames = Game::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        
        $rejectedPublishers = User::where('publisher_request_status', 'rejected')->get();
        
        $processedRefunds = RefundRequest::whereIn('status', ['approved', 'rejected'])
                            ->with(['user', 'game'])
                            ->orderBy('updated_at', 'desc')
                            ->get();

        // Untuk Game History, karena kita tidak punya kolom 'rejected',
        // Kita tampilkan game yang sudah Approved sebagai "Published History".
        $publishedGames = Game::where('is_approved', true)->orderBy('created_at', 'desc')->get();

        return view('admin.history', compact('rejectedGames', 'rejectedPublishers', 'processedRefunds', 'publishedGames'));
    }

    // --- GAME ACTIONS ---
    public function approveGame(Game $game)
    {
        $game->update(['is_approved' => true]);
        return back()->with('success', 'Game berhasil dipublish ke Store!');
    }

    public function rejectGame(Game $game)
    {
        // Hapus game dari database jika ditolak (Soft delete kalau ada, tapi di sini hard delete)
        // Atau biarkan tapi kasih flag. Kita pilih delete agar bersih, atau simpan di tabel history terpisah.
        // Sesuai request: "masukan ke database history".
        // Karena struktur tabel terbatas, kita simpan datanya di session flash atau log sebelum hapus, 
        // TAPI opsi terbaik tanpa ubah DB: Hapus recordnya.
        $game->delete(); 
        
        return back()->with('success', 'Game ditolak dan dihapus dari antrian.');
    }

    // --- PUBLISHER ACTIONS ---
    public function approvePublisher(User $user)
    {
        $user->role = 'publisher';
        $user->publisher_request_status = 'approved';
        $user->save();
        return redirect()->back()->with('success', 'User approved as Publisher.');
    }

    public function rejectPublisher(User $user)
    {
        $user->publisher_request_status = 'rejected';
        $user->save();
        return redirect()->back()->with('success', 'Publisher request rejected.');
    }

    // --- REFUND ACTIONS (UPDATE LOGIKA: SEMUA REFUND KE KUKUS MONEY) ---
    public function approveRefund($id)
    {
        $refund = RefundRequest::findOrFail($id);
        if ($refund->status !== 'pending') return back();

        $user = $refund->user;
        $gameId = $refund->game_id;

        // 1. Ambil harga pembelian dari tabel pivot game_user
        $purchaseData = DB::table('game_user')
            ->where('user_id', $user->id)
            ->where('game_id', $gameId)
            ->first();
        
        $refundAmount = $purchaseData ? (float)$purchaseData->purchase_price : 0.00;

        if ($refundAmount <= 0) {
            return back()->with('error', 'Gagal memproses refund: Harga pembelian tidak ditemukan atau 0.');
        }

        // 2. Setujui permintaan refund
        $refund->update(['status' => 'approved']);
        
        // 3. Hapus game dari library user (tabel pivot)
        $user->games()->detach($gameId);

        // 4. Tambahkan dana ke Kukus Money user (Sesuai permintaan)
        // Gunakan fungsi penambahan untuk menghindari masalah float.
        $user->increment('kukus_money_balance', $refundAmount);

        return back()->with('success', 'Refund disetujui. Saldo Kukus Money user ' . $user->name . ' ditambahkan sebesar Rp' . number_format($refundAmount, 0, ',', '.') . '.');
    }

    public function rejectRefund($id)
    {
        $refund = RefundRequest::findOrFail($id);
        $refund->update(['status' => 'rejected']);
        return back()->with('success', 'Refund ditolak.');
    }
}