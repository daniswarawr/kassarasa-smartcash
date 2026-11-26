<x-filament-panels::page>
    <div wire:poll.5s class="space-y-4">
        <div class="flex items-center justify-between rounded-xl bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800">
            <span>Layar dapur diperbarui otomatis setiap 5 detik.</span>
            <span>Terakhir cek: {{ now()->format('H:i:s') }}</span>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Kolom 1: Menunggu --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-lg bg-amber-100 px-3 py-2 font-bold text-amber-900">
                    <span>⏳ Menunggu</span>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs">{{ $this->getOrders('waiting')->count() }}</span>
                </div>
                @forelse($this->getOrders('waiting') as $order)
                    <div class="rounded-xl border border-amber-200 bg-white p-4 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono text-sm font-bold text-primary-700">{{ $order->transaction_code }}</span>
                                @if($order->customer_name)
                                    <p class="text-xs font-semibold text-gray-700">{{ $order->customer_name }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $order->transaction_date?->format('H:i') }}</span>
                        </div>
                        <ul class="space-y-1 border-t border-gray-100 pt-2 text-sm">
                            @foreach($order->transactionDetails as $detail)
                                <li class="flex justify-between">
                                    <span>{{ $detail->product->name ?? 'Menu' }}</span>
                                    <span class="font-bold">x{{ $detail->quantity }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            wire:click="updateStatus({{ $order->id }}, 'preparing')"
                            class="w-full rounded-lg bg-amber-500 px-3 py-2 text-xs font-bold text-white hover:bg-amber-600"
                        >
                            🔥 Mulai Masak
                        </button>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-xs text-gray-400">
                        Tidak ada pesanan menunggu.
                    </div>
                @endforelse
            </div>

            {{-- Kolom 2: Sedang Dimasak --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-lg bg-blue-100 px-3 py-2 font-bold text-blue-900">
                    <span>🍳 Sedang Dimasak</span>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs">{{ $this->getOrders('preparing')->count() }}</span>
                </div>
                @forelse($this->getOrders('preparing') as $order)
                    <div class="rounded-xl border border-blue-200 bg-white p-4 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono text-sm font-bold text-primary-700">{{ $order->transaction_code }}</span>
                                @if($order->customer_name)
                                    <p class="text-xs font-semibold text-gray-700">{{ $order->customer_name }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $order->transaction_date?->format('H:i') }}</span>
                        </div>
                        <ul class="space-y-1 border-t border-gray-100 pt-2 text-sm">
                            @foreach($order->transactionDetails as $detail)
                                <li class="flex justify-between">
                                    <span>{{ $detail->product->name ?? 'Menu' }}</span>
                                    <span class="font-bold">x{{ $detail->quantity }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            wire:click="updateStatus({{ $order->id }}, 'ready')"
                            class="w-full rounded-lg bg-primary-600 px-3 py-2 text-xs font-bold text-white hover:bg-primary-700"
                        >
                            ✅ Tandai Siap Saji
                        </button>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-xs text-gray-400">
                        Tidak ada pesanan sedang dimasak.
                    </div>
                @endforelse
            </div>

            {{-- Kolom 3: Siap Saji --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-lg bg-lime-100 px-3 py-2 font-bold text-lime-900">
                    <span>🛎️ Siap Saji</span>
                    <span class="rounded-full bg-white px-2 py-0.5 text-xs">{{ $this->getOrders('ready')->count() }}</span>
                </div>
                @forelse($this->getOrders('ready') as $order)
                    <div class="rounded-xl border border-lime-200 bg-white p-4 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-mono text-sm font-bold text-primary-700">{{ $order->transaction_code }}</span>
                                @if($order->customer_name)
                                    <p class="text-xs font-semibold text-gray-700">{{ $order->customer_name }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $order->transaction_date?->format('H:i') }}</span>
                        </div>
                        <ul class="space-y-1 border-t border-gray-100 pt-2 text-sm">
                            @foreach($order->transactionDetails as $detail)
                                <li class="flex justify-between">
                                    <span>{{ $detail->product->name ?? 'Menu' }}</span>
                                    <span class="font-bold">x{{ $detail->quantity }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <button
                            type="button"
                            wire:click="updateStatus({{ $order->id }}, 'served')"
                            class="w-full rounded-lg bg-lime-600 px-3 py-2 text-xs font-bold text-white hover:bg-lime-700"
                        >
                            🍽️ Selesai Disajikan
                        </button>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-gray-200 p-6 text-center text-xs text-gray-400">
                        Tidak ada pesanan siap saji.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>
