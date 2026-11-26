<?php

namespace App\Filament\Pages;

use App\Models\CashierShift;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ShiftKasir extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static string $view = 'filament.pages.shift-kasir';

    protected static ?string $navigationLabel = 'Shift & Tutup Kasir';

    protected static ?string $title = 'Manajemen Shift & Tutup Kasir';

    protected static ?string $navigationGroup = 'Smart POS';

    protected static ?int $navigationSort = 4;

    public float $opening_cash = 200000;

    public float $actual_cash = 0;

    public string $notes = '';

    public function getActiveShift(): ?CashierShift
    {
        return CashierShift::with('user')
            ->where('user_id', Auth::id())
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();
    }

    public function openShift(): void
    {
        if ($this->getActiveShift()) {
            Notification::make()->title('Shift masih terbuka')->warning()->send();
            return;
        }

        CashierShift::create([
            'user_id'       => Auth::id(),
            'opened_at'     => now(),
            'opening_cash'  => $this->opening_cash,
            'expected_cash' => $this->opening_cash,
            'status'        => 'open',
        ]);

        $this->actual_cash = $this->opening_cash;

        Notification::make()
            ->title('Shift kasir dibuka!')
            ->body('Modal awal: Rp ' . number_format($this->opening_cash, 0, ',', '.'))
            ->success()
            ->send();
    }

    public function closeShift(): void
    {
        $shift = $this->getActiveShift();
        if (! $shift) {
            return;
        }

        $diff = (float) $this->actual_cash - (float) $shift->expected_cash;

        $shift->update([
            'closed_at'   => now(),
            'actual_cash' => $this->actual_cash,
            'difference'  => $diff,
            'notes'       => $this->notes !== '' ? $this->notes : null,
            'status'      => 'closed',
        ]);

        $this->notes = '';
        $this->actual_cash = 0;

        Notification::make()
            ->title('Shift kasir ditutup!')
            ->body('Selisih kas: Rp ' . number_format($diff, 0, ',', '.'))
            ->success()
            ->send();
    }

    public function getShiftHistory()
    {
        return CashierShift::with('user')
            ->orderByDesc('opened_at')
            ->limit(15)
            ->get();
    }
}
