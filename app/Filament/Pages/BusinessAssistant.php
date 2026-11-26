<?php

namespace App\Filament\Pages;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class BusinessAssistant extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.pages.business-assistant';

    protected static ?string $navigationLabel = 'Business Assistant';

    protected static ?string $title = 'Smart Business Assistant';

    protected static ?string $navigationGroup = 'Keuangan & Laporan';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return Auth::user()?->isOwner() ?? true;
    }

    public function getIngredientPredictions(): array
    {
        $since = now()->subDays(7);
        $ingredients = Ingredient::with('recipes')->orderBy('stock', 'asc')->get();

        $results = [];
        foreach ($ingredients as $ing) {
            $usedLast7Days = 0;
            foreach ($ing->recipes as $recipe) {
                $soldQty = TransactionDetail::where('product_id', $recipe->product_id)
                    ->whereHas('transaction', fn ($q) => $q->where('status', 'completed')->where('transaction_date', '>=', $since))
                    ->sum('quantity');
                $usedLast7Days += (float) $soldQty * (float) $recipe->quantity_used;
            }

            $dailyAvg = $usedLast7Days / 7;
            $daysLeft = $dailyAvg > 0 ? round((float) $ing->stock / $dailyAvg, 1) : null;

            $advice = 'Stok aman.';
            $level = 'aman';
            if ((float) $ing->stock <= 0) {
                $advice = 'Stok habis! Segera lakukan pembelian ulang.';
                $level = 'habis';
            } elseif ((float) $ing->stock <= (float) $ing->min_stock || ($daysLeft !== null && $daysLeft <= 3)) {
                $daysText = $daysLeft !== null ? " (~{$daysLeft} hari lagi habis)" : '';
                $advice = "Stok menipis{$daysText}. Disarankan restock minimal " . number_format((float) $ing->min_stock * 2, 1) . " {$ing->unit}.";
                $level = 'menipis';
            }

            $results[] = [
                'name'       => $ing->name,
                'unit'       => $ing->unit,
                'stock'      => (float) $ing->stock,
                'daily_avg'  => round($dailyAvg, 2),
                'days_left'  => $daysLeft,
                'level'      => $level,
                'advice'     => $advice,
            ];
        }

        return $results;
    }

    public function getMenuInsights(): array
    {
        $since = now()->subDays(30);
        $products = Product::with('category')->where('status', 'available')->get();
        $insights = [];

        foreach ($products as $product) {
            $sold = (int) TransactionDetail::where('product_id', $product->id)
                ->whereHas('transaction', fn ($q) => $q->where('status', 'completed')->where('transaction_date', '>=', $since))
                ->sum('quantity');

            $price = (float) $product->price;
            $hpp   = (float) $product->hpp;
            $margin = $price > 0 ? round((($price - $hpp) / $price) * 100, 1) : 0;

            if ($sold >= 15 && $margin >= 40) {
                $tag = 'Bintang Utama';
                $color = 'success';
                $action = 'Pertahankan kualitas & jadikan menu rekomendasi kasir.';
            } elseif ($sold >= 15 && $margin < 40) {
                $tag = 'Laris, Margin Tipis';
                $color = 'warning';
                $action = 'Evaluasi HPP bahan baku atau pertimbangkan penyesuaian harga.';
            } elseif ($sold < 15 && $margin >= 40) {
                $tag = 'Potensial (Butuh Promo)';
                $color = 'info';
                $action = 'Buat paket bundling atau promosikan di jam ramai.';
            } else {
                $tag = 'Perlu Evaluasi';
                $color = 'danger';
                $action = 'Tinjau ulang resep, harga jual, atau minat pelanggan.';
            }

            $insights[] = [
                'name'   => $product->name,
                'sold'   => $sold,
                'margin' => $margin,
                'profit' => ($price - $hpp) * $sold,
                'tag'    => $tag,
                'color'  => $color,
                'action' => $action,
            ];
        }

        usort($insights, fn ($a, $b) => $b['sold'] <=> $a['sold']);
        return $insights;
    }

    public function getPeakHoursData(): array
    {
        $since = now()->subDays(30);
        $rows = Transaction::where('status', 'completed')
            ->where('transaction_date', '>=', $since)
            ->selectRaw('HOUR(transaction_date) as hr, COUNT(*) as total')
            ->groupBy('hr')
            ->pluck('total', 'hr')
            ->toArray();

        $labels = [];
        $data = [];
        for ($h = 8; $h <= 22; $h++) {
            $labels[] = sprintf('%02d:00', $h);
            $data[]   = (int) ($rows[$h] ?? 0);
        }

        $maxVal = max($data ?: [0]);
        $peakIndex = $maxVal > 0 ? array_search($maxVal, $data, true) : null;
        $peakHour = $peakIndex !== false && $peakIndex !== null ? $labels[$peakIndex] : '-';

        return [
            'labels'    => $labels,
            'data'      => $data,
            'peak_hour' => $peakHour,
            'peak_tx'   => $maxVal,
        ];
    }
}
