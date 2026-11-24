<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngredientResource\Pages;
use App\Models\Ingredient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IngredientResource extends Resource
{
    protected static ?string $model = Ingredient::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Smart Inventory (Stok Bahan)';

    protected static ?string $modelLabel = 'Bahan Baku';

    protected static ?string $pluralModelLabel = 'Stok Bahan Baku';

    protected static ?string $navigationGroup = 'Produk & Smart Inventory';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $lowCount = static::getModel()::whereColumn('stock', '<=', 'min_stock')->count();
        return $lowCount > 0 ? (string) $lowCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Bahan Baku')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Bahan Baku')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Ayam Fillet, Cabai Rawit, Beras'),

                        Forms\Components\Select::make('unit')
                            ->label('Satuan')
                            ->options([
                                'kg'    => 'Kilogram (kg)',
                                'gram'  => 'Gram (gr)',
                                'liter' => 'Liter (L)',
                                'ml'    => 'Mililiter (ml)',
                                'pcs'   => 'Pcs / Buah',
                                'pack'  => 'Pack / Bungkus',
                            ])
                            ->required()
                            ->default('kg'),
                    ]),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('stock')
                            ->label('Stok Saat Ini')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->default(0),

                        Forms\Components\TextInput::make('min_stock')
                            ->label('Batas Minimum (Peringatan)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.1)
                            ->default(5)
                            ->helperText('Notifikasi menipis muncul jika stok <= nilai ini.'),

                        Forms\Components\TextInput::make('cost_per_unit')
                            ->label('Harga Beli per Satuan')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->default(0),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok Tersedia')
                    ->sortable()
                    ->formatStateUsing(fn ($record) => number_format((float) $record->stock, 2, ',', '.') . ' ' . $record->unit)
                    ->badge()
                    ->color(fn (Ingredient $record): string => match ($record->stock_status) {
                        'habis'   => 'danger',
                        'menipis' => 'warning',
                        default   => 'success',
                    }),

                Tables\Columns\TextColumn::make('min_stock')
                    ->label('Batas Minimum')
                    ->formatStateUsing(fn ($record) => number_format((float) $record->min_stock, 2, ',', '.') . ' ' . $record->unit),

                Tables\Columns\TextColumn::make('cost_per_unit')
                    ->label('Harga / Satuan')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'habis'   => 'danger',
                        'menipis' => 'warning',
                        default   => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'habis'   => 'Habis',
                        'menipis' => 'Menipis',
                        default   => 'Aman',
                    }),
            ])
            ->defaultSort('stock', 'asc')
            ->filters([
                Tables\Filters\Filter::make('menipis')
                    ->label('Hanya Stok Menipis / Habis')
                    ->query(fn ($query) => $query->whereColumn('stock', '<=', 'min_stock')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Update Stok'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada data bahan baku')
            ->emptyStateDescription('Tambahkan bahan baku untuk memantau Smart Inventory.')
            ->emptyStateIcon('heroicon-o-archive-box')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListIngredients::route('/'),
            'create' => Pages\CreateIngredient::route('/create'),
            'edit'   => Pages\EditIngredient::route('/{record}/edit'),
        ];
    }
}
