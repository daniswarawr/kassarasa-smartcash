<x-filament-panels::page>
    {{-- Load Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <div class="space-y-6">

        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-funnel class="h-5 w-5 text-primary-500"/>
                        Filter Laporan
                    </div>
                    <button
                        type="button"
                        wire:click="exportCsv"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-primary-700"
                    >
                        📥 Export CSV / Excel
                    </button>
                </div>
            </x-slot>
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
            <x-filament::section>
                <div>
                    <p class="text-xs font-medium text-gray-500">Total Pendapatan</p>
                    <p class="mt-1 text-xl font-bold text-primary-700">
                        Rp {{ number_format($this->getTotalPendapatan(), 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">{{ number_format($this->getTotalTransaksi()) }} transaksi selesai</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div>
                    <p class="text-xs font-medium text-gray-500">Estimasi HPP & Laba Kotor</p>
                    <p class="mt-1 text-xl font-bold text-lime-700">
                        Rp {{ number_format($this->getLabaKotor(), 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">HPP: Rp {{ number_format($this->getTotalHpp(), 0, ',', '.') }}</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div>
                    <p class="text-xs font-medium text-gray-500">Biaya Operasional</p>
                    <p class="mt-1 text-xl font-bold text-rose-600">
                        Rp {{ number_format($this->getTotalBiayaOperasional(), 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Listrik, gas, gaji, sewa, dll.</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div>
                    <p class="text-xs font-medium text-gray-500">Estimasi Laba Bersih</p>
                    @php $net = $this->getLabaBersih(); @endphp
                    <p class="mt-1 text-xl font-bold {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        Rp {{ number_format($net, 0, ',', '.') }}
                    </p>
                    <p class="mt-1 text-xs text-gray-400">Laba kotor - biaya operasional</p>
                </div>
            </x-filament::section>
        </div>

        {{-- Grafik Penjualan --}}
        @php
            $chartData = $this->getChartData();
        @endphp
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-primary-500"/>
                    Grafik Penjualan Harian
                </div>
            </x-slot>

            <div
                class="relative h-72"
                wire:ignore
                x-data="{
                    chart: null,
                    labels: {{ json_encode($chartData['labels']) }},
                    data:   {{ json_encode($chartData['data']) }},
                    init() {
                        this.$nextTick(() => {
                            this.renderChart();
                        });
                    },
                    renderChart() {
                        if (this.chart) {
                            this.chart.destroy();
                        }
                        const ctx = this.$refs.canvas.getContext('2d');
                        this.chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: this.labels,
                                datasets: [{
                                    label: 'Pendapatan (Rp)',
                                    data: this.data,
                                    backgroundColor: 'rgba(14, 77, 66, 0.72)',
                                    borderColor: 'rgba(14, 77, 66, 1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    hoverBackgroundColor: 'rgba(101, 163, 13, 0.85)',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                                                if (value >= 1000) return 'Rp ' + (value/1000).toFixed(0) + 'rb';
                                                return 'Rp ' + value;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false }
                                    }
                                }
                            }
                        });
                    }
                }"
            >
                <canvas x-ref="canvas" class="w-full h-full"></canvas>
            </div>
        </x-filament::section>

        {{-- Produk Terlaris --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-fire class="h-5 w-5 text-orange-500"/>
                    Produk Terlaris (Top 5)
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 text-left font-semibold text-gray-600 dark:text-gray-300 w-10">No</th>
                            <th class="pb-3 text-left font-semibold text-gray-600 dark:text-gray-300">Produk</th>
                            <th class="pb-3 text-right font-semibold text-gray-600 dark:text-gray-300">Terjual</th>
                            <th class="pb-3 text-right font-semibold text-gray-600 dark:text-gray-300">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->getProdukTerlaris() as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold
                                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-500')) }}">
                                        {{ $index + 1 }}
                                    </span>
                                </td>
                                <td class="py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ $item->product->name ?? 'N/A' }}
                                </td>
                                <td class="py-3 text-right">
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                        {{ number_format($item->total_qty) }} pcs
                                    </span>
                                </td>
                                <td class="py-3 text-right font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <x-heroicon-o-inbox class="h-10 w-10"/>
                                        <span>Tidak ada data pada periode ini</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Daftar Transaksi --}}
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="h-5 w-5 text-primary-500"/>
                    Daftar Transaksi
                    <span class="ml-auto rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:bg-primary-900 dark:text-primary-300">
                        {{ $this->getTotalTransaksi() }} total
                    </span>
                </div>
            </x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-3 text-left font-semibold text-gray-600 dark:text-gray-300">Kode Transaksi</th>
                            <th class="pb-3 text-left font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                            <th class="pb-3 text-left font-semibold text-gray-600 dark:text-gray-300">Pembayaran</th>
                            <th class="pb-3 text-right font-semibold text-gray-600 dark:text-gray-300">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->getTransactions()->take(20) as $tx)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="py-3">
                                    <span class="font-mono text-xs font-semibold text-blue-600 dark:text-blue-400">
                                        {{ $tx->transaction_code }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-600 dark:text-gray-400">
                                    {{ $tx->transaction_date?->format('d M Y, H:i') }}
                                </td>
                                <td class="py-3">
                                    <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                        {{ match($tx->payment_method) {
                                            'cash'     => '💵 Tunai',
                                            'transfer' => '🏦 Transfer',
                                            'qris'     => '📱 QRIS',
                                            'debit'    => '💳 Debit',
                                            'credit'   => '💳 Kredit',
                                            default    => $tx->payment_method
                                        } }}
                                    </span>
                                </td>
                                <td class="py-3 text-right font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($tx->total_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-10 text-center">
                                    <div class="flex flex-col items-center gap-2 text-gray-400">
                                        <x-heroicon-o-inbox class="h-10 w-10"/>
                                        <span>Tidak ada transaksi pada periode ini</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @php $total = $this->getTransactions()->count(); @endphp
                @if($total > 20)
                    <div class="mt-4 rounded-lg bg-blue-50 p-3 text-center text-sm text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                        <x-heroicon-o-information-circle class="inline h-4 w-4 mr-1"/>
                        Menampilkan 20 dari <strong>{{ $total }}</strong> transaksi. Perkecil rentang tanggal untuk melihat lebih detail.
                    </div>
                @endif
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
