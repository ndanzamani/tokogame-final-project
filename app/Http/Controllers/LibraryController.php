<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Shelf;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    /**
     * Menampilkan halaman Library user.
     */
    public function index()
    {
        // Ambil semua game yang approved (Simulasi: user memiliki semua game ini)
        $ownedGames = Game::where('is_approved', true)->get();
        
        // Ambil Rak/Collection milik user yang sedang login
        $userShelves = Shelf::where('user_id', Auth::id())->with('games')->get();

        return view('library', compact('ownedGames', 'userShelves'));
    }

    /**
     * Membuat Shelf/Collection baru.
     */
    public function storeShelf(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mode' => 'required|in:manual,dynamic',
        ]);

        $shelf = Shelf::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'type' => $request->mode,
            'criteria' => $request->genre, // Disimpan jika mode dynamic
        ]);
    
        if ($request->mode === 'manual') {
            if ($request->has('selected_games')) {
                // Attach game yang dipilih ke shelf
                $shelf->games()->attach($request->selected_games);
            }
        } 
        elseif ($request->mode === 'dynamic') {
            // Cari game berdasarkan genre dan attach
            $gameIds = Game::where('genre', $request->genre)->pluck('id');
            $shelf->games()->attach($gameIds);
        }
    
        return redirect()->back()->with('success', 'Shelf berhasil dibuat!');
    }
}