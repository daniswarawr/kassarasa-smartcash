<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Carbon;

class LaporanPenjualan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.laporan-penjualan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $title = 'Laporan Penjualan, HPP & Laba Kotor';

    protected static ?string $navigationGroup = 'Keuangan & Laporan';

    protected static ?int $navigationSort = 2;

    public ?string $dari_tanggal = null;
    public ?string $sampai_tanggal = null;

    public function mount(): void
    {
        $this->dari_tanggal     = now()->startOfMonth()->toDateString();
        $this->sampai_tanggal   = now()->toDateString();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    DatePicker::make('dari_tanggal')
                        ->label('Dari Tanggal')
                        ->live(),
                    DatePicker::make('sampai_tanggal')
                        ->label('Sampai Tanggal')
                        ->live(),
                ]),
            ])
            ->statePath(null);
    }

    public function getTransactions()
    {
        return Transaction::with('transactionDetails.product')
            ->where('status', 'completed')
            ->when($this->dari_tanggal, fn ($q) => $q->whereDate('transaction_date', '>=', $this->dari_tanggal))
            ->when($this->sampai_tanggal, fn ($q) => $q->whereDate('transaction_date', '<=', $this->sampai_tanggal))
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    public function getTotalPendapatan(): float
    {
        return Transaction::where('status', 'completed')
            ->when($this->dari_tanggal, fn ($q) => $q->whereDate('transaction_date', '>=', $this->dari_tanggal))
            ->when($this->sampai_tanggal, fn ($q) => $q->whereDate('transaction_date', '<=', $this->sampai_tanggal))
            ->sum('total_price');
    }

    public function getTotalTransaksi(): int
    {
        return Transaction::where('status', 'completed')
            ->when($this->dari_tanggal, fn ($q) => $q->whereDate('transaction_date', '>=', $this->dari_tanggal))
            ->when($this->sampai_tanggal, fn ($q) => $q->whereDate('transaction_date', '<=', $this->sampai_tanggal))
            ->count();
    }

    public function getTotalHpp(): float
    {
        return (float) TransactionDetail::query()
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->when($this->dari_tanggal, fn ($q) => $q->whereDate('transactions.transaction_date', '>=', $this->dari_tanggal))
            ->when($this->sampai_tanggal, fn ($q) => $q->whereDate('transactions.transaction_date', '<=', $this->sampai_tanggal))
            ->selectRaw('COALESCE(SUM(transaction_details.quantity * products.hpp), 0) as total_hpp')
            ->value('total_hpp');
    }

    public function getLabaKotor(): float
    {
        return $this->getTotalPendapatan() - $this->getTotalHpp();
    }

    public function getProdukTerlaris(): \Illuminate\Support\Collection
    {
        return TransactionDetail::with('product')
            ->whereHas('transaction', function ($q) {
                $q->where('status', 'completed')
                    ->when($this->dari_tanggal, fn ($q) => $q->whereDate('transaction_date', '>=', $this->dari_tanggal))
                    ->when($this->sampai_tanggal, fn ($q) => $q->whereDate('transaction_date', '<=', $this->sampai_tanggal));
            })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    public function getChartData(): array
    {
        $dari    = Carbon::parse($this->dari_tanggal ?? now()->startOfMonth());
        $sampai  = Carbon::parse($this->sampai_tanggal ?? now());
        $labels  = [];
        $data    = [];

        $current = $dari->copy();
        while ($current->lte($sampai)) {
            $labels[] = $current->format('d M');
            $data[]   = (float) Transaction::where('status', 'completed')
                ->whereDate('transaction_date', $current->toDateString())
                ->sum('total_price');
            $current->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
