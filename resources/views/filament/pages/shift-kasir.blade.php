<x-filament-panels::page>
    <div class="space-y-6">
        @php $activeShift = $this->getActiveShift(); @endphp

        @if(! $activeShift)
            <x-filament::section>
                <x-slot name="heading">Buka Shift Kasir Baru</x-slot>
                <x-slot name="description">Masukkan modal uang tunai awal di laci kasir sebelum memulai transaksi.</x-slot>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="flex-1 max-w-sm">
                        <label class="mb-1 block text-sm font-medium text-gray-700">Modal Awal Laci Kas (Rp)</label>
                        <input
                            type="number"
                            min="0"
                            step="10000"
                            wire:model="opening_cash"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                    <button
                        type="button"
                        wire:click="openShift"
                        class="rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                    >
                        🔓 Buka Shift Sekarang
                    </button>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <x-slot name="heading">Shift Aktif — {{ $activeShift->user->name ?? 'Kasir' }}</x-slot>
                <x-slot name="description">Dibuka pada {{ $activeShift->opened_at?->format('d M Y, H:i') }}</x-slot>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mb-6">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="text-xs text-gray-500">Modal Awal</p>
                        <p class="mt-1 text-xl font-bold text-gray-900">Rp {{ number_format((float)$activeShift->opening_cash, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs text-emerald-700">Penjualan Tunai Shift Ini</p>
                        <p class="mt-1 text-xl font-bold text-primary-700">
                            Rp {{ number_format((float)$activeShift->expected_cash - (float)$activeShift->opening_cash, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-lime-50 p-4">
                        <p class="text-xs text-lime-800">Kas Sistem Seharusnya</p>
                        <p class="mt-1 text-xl font-bold text-lime-800">Rp {{ number_format((float)$activeShift->expected_cash, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 items-end border-t border-gray-200 pt-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Uang Fisik di Laci (Rp)</label>
                        <input
                            type="number"
                            min="0"
                            step="1000"
                            wire:model="actual_cash"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Catatan Tutup Kasir</label>
                        <input
                            type="text"
                            wire:model="notes"
                            placeholder="Opsional (cth: selisih kembalian)"
                            class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        />
                    </div>
                    <div>
                        <button
                            type="button"
                            wire:click="closeShift"
                            class="w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700"
                        >
                            🔒 Tutup Kasir & Simpan Rekap
                        </button>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Riwayat Shift --}}
        <x-filament::section>
            <x-slot name="heading">Riwayat Shift Kasir</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-600">
                            <th class="pb-3">Kasir</th>
                            <th class="pb-3">Buka</th>
                            <th class="pb-3">Tutup</th>
                            <th class="pb-3 text-right">Kas Sistem</th>
                            <th class="pb-3 text-right">Kas Fisik</th>
                            <th class="pb-3 text-right">Selisih</th>
                            <th class="pb-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($this->getShiftHistory() as $shift)
                            <tr>
                                <td class="py-3 font-medium">{{ $shift->user->name ?? 'Kasir' }}</td>
                                <td class="py-3 text-gray-600">{{ $shift->opened_at?->format('d M, H:i') }}</td>
                                <td class="py-3 text-gray-600">{{ $shift->closed_at?->format('d M, H:i') ?? '-' }}</td>
                                <td class="py-3 text-right">Rp {{ number_format((float)$shift->expected_cash, 0, ',', '.') }}</td>
                                <td class="py-3 text-right">{{ $shift->actual_cash !== null ? 'Rp ' . number_format((float)$shift->actual_cash, 0, ',', '.') : '-' }}</td>
                                <td class="py-3 text-right font-semibold {{ (float)$shift->difference < 0 ? 'text-rose-600' : 'text-lime-700' }}">
                                    {{ $shift->status === 'closed' ? 'Rp ' . number_format((float)$shift->difference, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $shift->status === 'open' ? 'bg-lime-100 text-lime-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ $shift->status === 'open' ? 'Aktif' : 'Ditutup' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-gray-400">Belum ada riwayat shift kasir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
