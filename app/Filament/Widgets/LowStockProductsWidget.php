<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Produk Stok Menipis (≤ 5)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()->where('stock', '<=', 5)->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->weight('bold')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Tersisa')
                    ->badge()
                    ->color(fn (Product $record): string => $record->stock <= 0 ? 'danger' : 'warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available'   => 'success',
                        'unavailable' => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available'   => 'Tersedia',
                        'unavailable' => 'Tidak Tersedia',
                        default       => $state,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Update Stok')
                    ->url(fn (Product $record) => route('filament.admin.resources.products.edit', $record))
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning'),
            ])
            ->emptyStateHeading('Semua stok aman')
            ->emptyStateDescription('Tidak ada produk dengan stok ≤ 5.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }
}
