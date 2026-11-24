<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Daftar Menu --}}
        <div class="space-y-4 lg:col-span-2">
            <x-filament::section>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Cari menu makanan atau minuman..."
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:max-w-xs"
                    />

                    <div class="flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="$set('selectedCategoryId', null)"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition {{ is_null($selectedCategoryId) ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                        >
                            Semua
                        </button>
                        @foreach($this->getCategories() as $category)
                            <button
                                type="button"
                                wire:click="$set('selectedCategoryId', {{ $category->id }})"
                                class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $selectedCategoryId === $category->id ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </x-filament::section>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($this->getProducts() as $product)
                    <div
                        wire:click="addToCart({{ $product->id }})"
                        class="cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-primary-500 hover:shadow-md"
                    >
                        @if($product->image)
                            <div class="aspect-square w-full overflow-hidden bg-gray-100">
                                <img
                                    src="{{ asset('storage/' . $product->image) }}"
                                    alt="{{ $product->name }}"
                                    class="h-full w-full object-cover transition duration-300 hover:scale-105"
                                />
                            </div>
                        @endif
                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                                        {{ $product->category->name ?? 'Menu' }}
                                    </span>
                                    <h3 class="mt-1.5 font-bold text-gray-900">{{ $product->name }}</h3>
                                </div>
                                <span class="shrink-0 rounded-md px-2 py-0.5 text-xs font-semibold {{ $product->stock <= 5 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700' }}">
                                    Stok: {{ $product->stock }}
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-base font-bold text-primary-700">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                                <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1 text-xs font-semibold text-primary-700">
                                    <x-heroicon-o-plus class="h-3.5 w-3.5"/>
                                    Tambah
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                        Tidak ada produk tersedia untuk kategori / kata kunci ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kolom Kanan: Keranjang & Pembayaran --}}
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">Pesanan / Keranjang</x-slot>

                @if(empty($cart))
                    <div class="py-8 text-center text-sm text-gray-400">
                        Klik menu di sebelah kiri untuk menambahkan ke transaksi.
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($cart as $id => $item)
                            <div class="py-3 flex items-center justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-gray-900">{{ $item['name'] }}</p>
                                    <p class="text-xs text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="decrementQty({{ $id }})" class="h-7 w-7 rounded-md bg-gray-100 text-sm font-bold hover:bg-gray-200">-</button>
                                    <span class="w-6 text-center text-sm font-semibold">{{ $item['qty'] }}</span>
                                    <button type="button" wire:click="incrementQty({{ $id }})" class="h-7 w-7 rounded-md bg-gray-100 text-sm font-bold hover:bg-gray-200">+</button>
                                    <button type="button" wire:click="removeFromCart({{ $id }})" class="ml-1 text-rose-500 hover:text-rose-700">
                                        <x-heroicon-o-trash class="h-4 w-4"/>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 space-y-3 border-t border-gray-200 pt-4">
                        <div class="flex justify-between text-base font-bold text-gray-900">
                            <span>TOTAL PEMBAYARAN</span>
                            <span class="text-primary-700">Rp {{ number_format($this->getTotal(), 0, ',', '.') }}</span>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Metode Pembayaran</label>
                            <select wire:model.live="payment_method" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="cash">Tunai (Cash)</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="debit">Kartu Debit</option>
                                <option value="credit">Kartu Kredit</option>
                            </select>
                        </div>

                        @if($payment_method === 'cash')
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-600">Nominal Bayar (Rp)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1000"
                                    wire:model.live="cash_received"
                                    placeholder="Masukkan uang diterima..."
                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            @if($cash_received > 0)
                                <div class="flex justify-between rounded-lg bg-lime-50 px-3 py-2 text-sm font-semibold text-lime-800">
                                    <span>Kembalian</span>
                                    <span>Rp {{ number_format($this->getChange(), 0, ',', '.') }}</span>
                                </div>
                            @endif
                        @endif

                        <div class="flex gap-2 pt-2">
                            <button
                                type="button"
                                wire:click="clearCart"
                                class="rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Reset
                            </button>
                            <button
                                type="button"
                                wire:click="checkout"
                                class="flex-1 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                            >
                                Bayar & Simpan Transaksi
                            </button>
                        </div>
                    </div>
                @endif
            </x-filament::section>

            {{-- Struk Transaksi Terakhir --}}
            @if($lastTx = $this->getLastTransaction())
                <x-filament::section>
                    <x-slot name="heading">Struk Transaksi Terakhir</x-slot>
                    <div class="space-y-2 text-xs text-gray-700">
                        <div class="flex justify-between font-mono font-bold text-primary-700">
                            <span>{{ $lastTx->transaction_code }}</span>
                            <span>{{ $lastTx->transaction_date?->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="border-t border-dashed border-gray-300 pt-2 space-y-1">
                            @foreach($lastTx->transactionDetails as $detail)
                                <div class="flex justify-between">
                                    <span>{{ $detail->product->name ?? 'Item' }} x{{ $detail->quantity }}</span>
                                    <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-dashed border-gray-300 pt-2 flex justify-between text-sm font-bold text-gray-900">
                            <span>Total</span>
                            <span>Rp {{ number_format($lastTx->total_price, 0, ',', '.') }}</span>
                        </div>
                        <div class="pt-2 text-right">
                            <a
                                href="{{ route('filament.admin.resources.transactions.view', $lastTx) }}"
                                class="text-xs font-semibold text-primary-700 hover:underline"
                            >
                                Lihat Detail Transaksi →
                            </a>
                        </div>
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
