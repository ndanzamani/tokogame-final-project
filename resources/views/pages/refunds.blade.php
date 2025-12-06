<x-app-layout>
    <div class="py-12 bg-[#242629] min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Alert Messages --}}
            @if(session('success'))
                <div class="bg-green-600 text-white p-4 rounded mb-6 border border-green-400 shadow-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-600 text-white p-4 rounded mb-6 border border-red-400 shadow-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-[#16161a] border border-black shadow-2xl p-8">
                <h1 class="text-3xl font-black text-white mb-2 uppercase tracking-wide">Kukus Refunds</h1>
                <p class="text-gray-400 text-sm mb-8">
                    Anda dapat mengajukan refund untuk game yang sudah dibeli dan belum memiliki permintaan yang tertunda (pending).
                </p>

                {{-- FORM REFUND --}}
                <form action="{{ route('refunds.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    {{-- Select Game --}}
                    <div>
                        <label class="block text-[#66c0f4] text-sm font-bold uppercase tracking-wider mb-2">Pilih Game untuk di-Refund</label>
                        <select name="game_id" class="w-full bg-[#242629] text-white border border-[#7f5af0] rounded-sm p-2 focus:ring-0 focus:border-white h-12">
                            @forelse($games as $game)
                                <option value="{{ $game->id }}">
                                    {{ $game->title }} 
                                    (Harga Beli: Rp {{ number_format($game->pivot->purchase_price, 0, ',', '.') }}, 
                                    Dibeli: {{ $game->pivot->created_at->format('d M Y') }})
                                </option>
                            @empty
                                <option disabled>Anda tidak memiliki game yang memenuhi syarat untuk di-refund.</option>
                            @endforelse
                        </select>
                        @error('game_id')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Reason --}}
                    <div>
                        <label class="block text-[#66c0f4] text-sm font-bold uppercase tracking-wider mb-2">Alasan Refund</label>
                        <textarea name="reason" rows="4" class="w-full bg-[#242629] text-white border border-[#7f5af0] rounded-sm p-3 focus:ring-0 focus:border-white" placeholder="Jelaskan mengapa Anda ingin mengembalikan game ini..."></textarea>
                        @error('reason')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-4 border-t border-gray-700">
                        <button type="submit" 
                            @if($games->isEmpty()) disabled @endif
                            class="bg-gradient-to-r from-[#7f5af0] to-[#7f5af0] hover:brightness-110 text-black font-black py-3 px-6 rounded-sm shadow-md w-full uppercase tracking-widest transition disabled:opacity-50 disabled:cursor-not-allowed">
                            Kirim Permintaan Refund
                        </button>
                        @if($games->isEmpty())
                             <p class="text-xs text-red-400 mt-2 text-center">Tidak ada game yang bisa di-refund saat ini (Mungkin Library kosong atau sudah ada permintaan pending).</p>
                        @endif
                    </div>
                </form>

                {{-- HISTORY REFUND --}}
                <div class="mt-10 pt-6 border-t border-gray-700">
                    <h3 class="text-xl font-black text-white mb-4 uppercase tracking-wider">Riwayat Refund Anda</h3>
                    <div class="space-y-3">
                        @forelse($history as $request)
                        <div class="p-3 rounded-sm border 
                            @if($request->status == 'pending') bg-yellow-900/30 border-yellow-500 text-yellow-300 
                            @elseif($request->status == 'approved') bg-green-900/30 border-green-500 text-green-300
                            @else bg-red-900/30 border-red-500 text-red-300 @endif
                            ">
                            <div class="flex justify-between items-center text-sm font-bold">
                                <span>{{ $request->game->title }}</span>
                                <span class="uppercase">{{ $request->status }}</span>
                            </div>
                            <p class="text-xs italic mt-1">{{ $request->reason }}</p>
                            <p class="text-[10px] mt-1">Diajukan: {{ $request->created_at->format('d M Y') }}</p>
                        </div>
                        @empty
                            <p class="text-gray-500 text-sm italic">Belum ada riwayat permintaan refund.</p>
                        @endforelse
                    </div>
                </div>

            </div>
            
            <div class="mt-8 text-center">
                <a href="{{ route('store.index') }}" class="text-gray-500 hover:text-white text-xs">Kembali ke Store</a>
            </div>
        </div>
    </div>
</x-app-layout>