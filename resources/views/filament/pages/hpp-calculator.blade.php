<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Kolom Kiri: Input Komponen HPP --}}
        <div class="space-y-6 lg:col-span-2">
            <x-filament::section>
                <x-slot name="heading">1. Pilih Produk / Menu (Opsional)</x-slot>
                <x-slot name="description">Pilih menu yang ingin dihitung HPP-nya, atau lakukan simulasi bebas di bawah.</x-slot>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Produk / Menu</label>
                        <select
                            wire:model.live="selected_product_id"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="">-- Simulasi Bebas (Tanpa Produk) --</option>
                            @foreach($this->getProducts() as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} (Harga: Rp {{ number_format($product->price, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Harga Jual per Porsi (Rp)</label>
                        <input
                            type="number"
                            min="0"
                            step="500"
                            wire:model.live="selling_price"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">2. Komponen Biaya Bahan & Kemasan per Porsi</x-slot>

                <div class="space-y-3">
                    @foreach($items as $index => $item)
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input
                                type="text"
                                wire:model.live="items.{{ $index }}.name"
                                placeholder="Nama bahan / biaya (cth: Beras, Ayam, Kemasan)"
                                class="flex-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">Rp</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="100"
                                    wire:model.live="items.{{ $index }}.cost"
                                    class="w-36 rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $index }})"
                                    class="rounded-lg bg-rose-50 p-2 text-rose-600 hover:bg-rose-100"
                                    title="Hapus baris"
                                >
                                    <x-heroicon-o-trash class="h-4 w-4"/>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="addItem"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-700"
                    >
                        <x-heroicon-o-plus class="h-4 w-4"/>
                        Tambah Komponen
                    </button>

                    @if($selected_product_id)
                        <button
                            type="button"
                            wire:click="saveToProduct"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-lime-600 px-3 py-2 text-sm font-semibold text-white hover:bg-lime-700"
                        >
                            <x-heroicon-o-check-circle class="h-4 w-4"/>
                            Simpan HPP & Harga ke Produk
                        </button>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Kolom Kanan: Ringkasan & Simulasi (Sesuai Slide 9 PPT) --}}
        <div class="space-y-6">
            @php
                $hppPerPorsi = $this->getHppPerPortion();
                $labaPerPorsi = $this->getGrossProfitPerPortion();
                $margin = $this->getMarginPercent();
                $porsi = max(1, (int) $simulation_portions);
            @endphp

            <x-filament::section>
                <x-slot name="heading">Perhitungan HPP per Porsi</x-slot>

                <div class="space-y-2 text-sm">
                    @foreach($items as $item)
                        @if(!empty($item['name']) || (float)($item['cost'] ?? 0) > 0)
                            <div class="flex justify-between text-gray-600">
                                <span>{{ $item['name'] ?: 'Komponen' }}</span>
                                <span class="font-medium">Rp {{ number_format((float)($item['cost'] ?? 0), 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endforeach

                    <div class="my-3 border-t border-gray-200 pt-3 flex justify-between text-base font-bold text-gray-900">
                        <span>HPP per Porsi</span>
                        <span class="text-primary-700">Rp {{ number_format($hppPerPorsi, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Harga Jual</span>
                        <span class="font-semibold">Rp {{ number_format((float)$selling_price, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-base font-bold {{ $labaPerPorsi >= 0 ? 'text-lime-700' : 'text-rose-600' }}">
                        <span>Laba Kotor / Porsi</span>
                        <span>Rp {{ number_format($labaPerPorsi, 0, ',', '.') }} ({{ $margin }}%)</span>
                    </div>
                </div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Simulasi Penjualan</x-slot>

                <div class="mb-4">
                    <label class="mb-1 block text-xs font-medium text-gray-600">Jumlah Porsi Simulasi</label>
                    <input
                        type="number"
                        min="1"
                        wire:model.live="simulation_portions"
                        class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    />
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Penjualan ({{ $porsi }} porsi)</span>
                        <span class="font-semibold">Rp {{ number_format((float)$selling_price * $porsi, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total HPP ({{ $porsi }} porsi)</span>
                        <span class="font-semibold">Rp {{ number_format($hppPerPorsi * $porsi, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-gray-200 pt-2 flex justify-between text-base font-bold text-lime-700">
                        <span>Laba Kotor</span>
                        <span>Rp {{ number_format($labaPerPorsi * $porsi, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-emerald-950 p-3 text-xs text-emerald-100">
                    <strong>Catatan:</strong> Laba kotor belum dikurangi biaya operasional sehingga bukan laba bersih.
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
