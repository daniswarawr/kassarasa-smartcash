<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPendapatan = (float) Transaction::where('status', 'completed')->sum('total_price');
        $totalTransaksi  = Transaction::where('status', 'completed')->count();

        $totalHpp = (float) TransactionDetail::query()
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->selectRaw('COALESCE(SUM(transaction_details.quantity * products.hpp), 0) as total_hpp')
            ->value('total_hpp');

        $labaKotor = $totalPendapatan - $totalHpp;

        $lowStockProducts    = Product::where('stock', '<=', 5)->count();
        $lowStockIngredients = Ingredient::whereColumn('stock', '<=', 'min_stock')->count();

        // Transaksi bulan ini vs bulan lalu
        $txBulanIni = Transaction::where('status', 'completed')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->count();
        $txBulanLalu = Transaction::where('status', 'completed')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->count();

        $txTrend = $txBulanLalu > 0
            ? round((($txBulanIni - $txBulanLalu) / $txBulanLalu) * 100, 1)
            : 0;

        // Pendapatan bulan ini vs bulan lalu
        $pendapatanBulanIni = Transaction::where('status', 'completed')
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('total_price');
        $pendapatanBulanLalu = Transaction::where('status', 'completed')
            ->whereMonth('transaction_date', now()->subMonth()->month)
            ->whereYear('transaction_date', now()->subMonth()->year)
            ->sum('total_price');

        $pendapatanTrend = $pendapatanBulanLalu > 0
            ? round((($pendapatanBulanIni - $pendapatanBulanLalu) / $pendapatanBulanLalu) * 100, 1)
            : 0;

        return [
            Stat::make('Total Penjualan', 'Rp ' . number_format($totalPendapatan, 0, ',', '.'))
                ->description($pendapatanTrend >= 0 ? "+{$pendapatanTrend}% dari bulan lalu" : "{$pendapatanTrend}% dari bulan lalu")
                ->descriptionIcon($pendapatanTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pendapatanTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3, 4, 7, 8, 5]),

            Stat::make('Total Transaksi', number_format($totalTransaksi))
                ->description($txTrend >= 0 ? "+{$txTrend}% dari bulan lalu" : "{$txTrend}% dari bulan lalu")
                ->descriptionIcon($txTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('primary')
                ->chart([3, 5, 4, 7, 2, 5, 8, 3, 6, 4, 7, 5]),

            Stat::make('Estimasi Laba Kotor', 'Rp ' . number_format($labaKotor, 0, ',', '.'))
                ->description('Penjualan dikurangi HPP produk')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Stok Menipis', ($lowStockProducts + $lowStockIngredients) . ' item')
                ->description("{$lowStockProducts} produk, {$lowStockIngredients} bahan baku")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(($lowStockProducts + $lowStockIngredients) > 0 ? 'warning' : 'success'),
        ];
    }
}
