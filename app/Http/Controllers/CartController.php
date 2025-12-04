<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; // Import Auth
use Illuminate\Support\Str; // Import Str untuk transaction ID
use Barryvdh\DomPDF\Facade\Pdf; // Asumsi menggunakan library DomPDF (jika ada) - kalau tidak, gunakan simulasi download

class CartController extends Controller
{
    // Menampilkan isi keranjang (TIDAK ADA PERUBAHAN)
    public function index()
    {
        $cart = Session::get('cart', []);
        
        $total = 0;
        foreach($cart as $item) {
            $price = $item['price'];
            if(isset($item['discount_percent']) && $item['discount_percent'] > 0) {
                $price = $price * (1 - $item['discount_percent'] / 100);
            }
            $total += $price;
        }

        return view('cart.index', compact('cart', 'total'));
    }

    // Menambahkan game ke keranjang (TIDAK ADA PERUBAHAN)
    public function addToCart($id)
    {
        $game = Game::findOrFail($id);
        $cart = Session::get('cart', []);

        if(!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk membeli game.');
        }

        // Cek apakah game sudah dimiliki
        $user = Auth::user();
        if ($user->games->contains($id)) {
            return redirect()->back()->with('error', 'Anda sudah memiliki game ini!');
        }

        // Cek jika game sudah ada di cart
        if(isset($cart[$id])) {
            return redirect()->back()->with('error', 'Game ini sudah ada di keranjang Anda!');
        }

        // Simpan data game ke session cart
        $cart[$id] = [
            'id' => $game->id,
            'title' => $game->title,
            'price' => $game->price,
            'cover_image' => $game->cover_image,
            'discount_percent' => $game->discount_percent
        ];

        Session::put('cart', $cart);

        return redirect()->route('cart.index')->with('success', 'Game berhasil ditambahkan ke keranjang!');
    }

    // Menghapus game dari keranjang (TIDAK ADA PERUBAHAN)
    public function remove($id)
    {
        $cart = Session::get('cart', []);

        if(isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Game dihapus dari keranjang.');
    }

    // Halaman Checkout (TIDAK ADA PERUBAHAN)
    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if(empty($cart)) {
            return redirect()->route('store.index')->with('error', 'Keranjang Anda kosong.');
        }

        $total = 0;
        foreach($cart as $item) {
            $price = $item['price'];
            if(isset($item['discount_percent']) && $item['discount_percent'] > 0) {
                $price = $price * (1 - $item['discount_percent'] / 100);
            }
            $total += $price;
        }

        return view('cart.checkout', compact('cart', 'total'));
    }

    // Process Pembayaran (PERBAIKAN LOGIKA)
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required',
        ]);

        $cart = Session::get('cart', []);
        if(empty($cart)) {
            return redirect()->route('store.index')->with('error', 'Keranjang Anda kosong.');
        }

        $user = Auth::user();
        $transactionId = 'TXN-' . strtoupper(Str::random(10));
        $totalTransaction = 0;
        $purchasedGames = [];
        
        // 1. Simpan Transaksi & Tambahkan ke Library
        foreach ($cart as $item) {
            $price = $item['price'];
            if(isset($item['discount_percent']) && $item['discount_percent'] > 0) {
                $price = $price * (1 - $item['discount_percent'] / 100);
            }
            
            // Tambahkan game ke library user (tabel pivot game_user)
            try {
                $user->games()->attach($item['id'], [
                    'purchase_price' => $price,
                    'transaction_id' => $transactionId, // Gunakan ID transaksi yang sama untuk semua item
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Abaikan jika game sudah dimiliki (unique constraint violation)
                // Seharusnya sudah dicek di addToCart, tapi ini sebagai fallback.
                continue; 
            }
            
            $totalTransaction += $price;
            $purchasedGames[] = array_merge($item, ['final_price' => $price]);
        }

        // 2. Siapkan data transaksi untuk halaman sukses
        $transactionData = [
            'id' => $transactionId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'total' => $totalTransaction,
            'method' => $request->payment_method,
            'date' => now()->format('d F Y H:i:s'),
            'items' => $purchasedGames,
        ];

        Session::put('last_transaction', $transactionData);
        Session::forget('cart');

        return redirect()->route('cart.success');
    }

    // Halaman Sukses (UPDATE)
    public function success()
    {
        $transaction = Session::get('last_transaction');

        if (!$transaction) {
            return redirect()->route('store.index')->with('error', 'Tidak ada riwayat transaksi ditemukan.');
        }

        // Kita biarkan session 'last_transaction' tetap ada agar bisa direfresh
        // Jika butuh download receipt, kita buat route baru
        return view('cart.success', compact('transaction'));
    }

    // FITUR BARU: Download Receipt/Nota
    public function downloadReceipt()
    {
        $transaction = Session::get('last_transaction');

        if (!$transaction) {
            return redirect()->route('store.index');
        }

        // Kita akan menggunakan template sederhana untuk receipt, 
        // karena tidak ada library PDF yang terinstal, kita akan buat file teks/HTML yang didownload.
        
        $receiptContent = "========================================\n";
        $receiptContent .= "         STEAMCLONE RECEIPT\n";
        $receiptContent .= "========================================\n";
        $receiptContent .= "Transaction ID: " . $transaction['id'] . "\n";
        $receiptContent .= "Date: " . $transaction['date'] . "\n";
        $receiptContent .= "Customer: " . $transaction['user_name'] . "\n";
        $receiptContent .= "Payment Method: " . ucfirst($transaction['method']) . "\n";
        $receiptContent .= "----------------------------------------\n";
        $receiptContent .= "ITEMS:\n";
        
        foreach ($transaction['items'] as $item) {
            $receiptContent .= "- " . $item['title'] . ": Rp " . number_format($item['final_price'], 0, ',', '.') . "\n";
        }

        $receiptContent .= "----------------------------------------\n";
        $receiptContent .= "TOTAL: Rp " . number_format($transaction['total'], 0, ',', '.') . "\n";
        $receiptContent .= "========================================\n";
        $receiptContent .= "Thank you for your purchase!\n";

        // Mengirim response sebagai file download
        return response($receiptContent, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="receipt-' . $transaction['id'] . '.txt"');
    }
}