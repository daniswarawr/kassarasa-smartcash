<?php

namespace App\Filament\Pages;

use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KitchenDisplay extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static string $view = 'filament.pages.kitchen-display';

    protected static ?string $navigationLabel = 'Antrean Dapur';

    protected static ?string $title = 'Antrean Dapur (Kitchen Display)';

    protected static ?string $navigationGroup = 'Smart POS';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $active = Transaction::where('status', 'completed')
            ->whereIn('kitchen_status', ['waiting', 'preparing'])
            ->count();

        return $active > 0 ? (string) $active : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function updateStatus(int $transactionId, string $nextStatus): void
    {
        $tx = Transaction::find($transactionId);
        if (! $tx) {
            return;
        }

        $tx->update(['kitchen_status' => $nextStatus]);

        $label = match ($nextStatus) {
            'preparing' => 'Sedang Dimasak',
            'ready'     => 'Siap Saji',
            'served'    => 'Sudah Disajikan',
            default     => $nextStatus,
        };

        Notification::make()
            ->title("{$tx->transaction_code} → {$label}")
            ->success()
            ->send();
    }

    public function getOrders(string $kitchenStatus)
    {
        return Transaction::with('transactionDetails.product')
            ->where('status', 'completed')
            ->where('kitchen_status', $kitchenStatus)
            ->orderBy('transaction_date', 'asc')
            ->limit(20)
            ->get();
    }
}
