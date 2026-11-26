<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    @php
        $predictions = $this->getIngredientPredictions();
        $menuInsights = $this->getMenuInsights();
        $peakData = $this->getPeakHoursData();
    @endphp

    <div class="space-y-6">
        {{-- Highlight Jam Ramai --}}
        <x-filament::section>
            <x-slot name="heading">📊 Analisis Jam Ramai (30 Hari Terakhir)</x-slot>
            <x-slot name="description">
                Jam paling sibuk tercatat pada pukul <strong>{{ $peakData['peak_hour'] }}</strong> ({{ $peakData['peak_tx'] }} transaksi). Siapkan stok & kasir lebih awal sebelum jam tersebut.
            </x-slot>

            <div class="relative h-64" wire:ignore x-data="{
                init() {
                    const ctx = this.$refs.canvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: {{ json_encode($peakData['labels']) }},
                            datasets: [{
                                label: 'Jumlah Transaksi',
                                data: {{ json_encode($peakData['data']) }},
                                backgroundColor: 'rgba(14, 77, 66, 0.78)',
                                borderRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            }">
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>
        </x-filament::section>

        {{-- Prediksi Stok Bahan Baku --}}
        <x-filament::section>
            <x-slot name="heading">🧠 Prediksi Kebutuhan & Stok Bahan Baku</x-slot>
            <x-slot name="description">Dihitung dari stok saat ini dan rata-rata pemakaian resep 7 hari terakhir.</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-600">
                            <th class="pb-3">Bahan Baku</th>
                            <th class="pb-3 text-right">Stok Saat Ini</th>
                            <th class="pb-3 text-right">Pemakaian / Hari</th>
                            <th class="pb-3">Rekomendasi Assistant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($predictions as $item)
                            <tr>
                                <td class="py-3 font-semibold text-gray-900">{{ $item['name'] }}</td>
                                <td class="py-3 text-right">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $item['level'] === 'habis' ? 'bg-rose-100 text-rose-700' : ($item['level'] === 'menipis' ? 'bg-amber-100 text-amber-800' : 'bg-lime-100 text-lime-800') }}">
                                        {{ number_format($item['stock'], 1, ',', '.') }} {{ $item['unit'] }}
                                    </span>
                                </td>
                                <td class="py-3 text-right text-gray-600">
                                    {{ $item['daily_avg'] > 0 ? number_format($item['daily_avg'], 2, ',', '.') . ' ' . $item['unit'] : '-' }}
                                </td>
                                <td class="py-3 text-gray-700">{{ $item['advice'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Matriks Performa Menu --}}
        <x-filament::section>
            <x-slot name="heading">🍽️ Matriks Performa & Rekomendasi Menu (30 Hari)</x-slot>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-600">
                            <th class="pb-3">Menu</th>
                            <th class="pb-3 text-right">Terjual</th>
                            <th class="pb-3 text-right">Margin</th>
                            <th class="pb-3">Kategori</th>
                            <th class="pb-3">Saran Strategi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($menuInsights as $row)
                            <tr>
                                <td class="py-3 font-semibold text-gray-900">{{ $row['name'] }}</td>
                                <td class="py-3 text-right font-medium">{{ $row['sold'] }} porsi</td>
                                <td class="py-3 text-right font-semibold">{{ $row['margin'] }}%</td>
                                <td class="py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                        {{ $row['color'] === 'success' ? 'bg-lime-100 text-lime-800' : ($row['color'] === 'warning' ? 'bg-amber-100 text-amber-800' : ($row['color'] === 'info' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-700')) }}">
                                        {{ $row['tag'] }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-600">{{ $row['action'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
