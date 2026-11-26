<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Produk / Menu';

    protected static ?string $modelLabel = 'Produk';

    protected static ?string $pluralModelLabel = 'Produk / Menu';

    protected static ?string $navigationGroup = 'Produk & Smart Inventory';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->isOwner() ?? true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Produk')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Nasi Goreng Spesial'),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih Kategori'),
                    ]),

                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi Produk')
                        ->placeholder('Deskripsi lengkap produk...')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Harga, HPP & Stok')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Harga Jual (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp')
                            ->placeholder('0'),

                        Forms\Components\TextInput::make('hpp')
                            ->label('HPP / Modal (Rp)')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('Rp')
                            ->helperText('Bisa diisi otomatis via HPP Calculator'),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stok')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->placeholder('0'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available'   => 'Tersedia',
                                'unavailable' => 'Tidak Tersedia',
                            ])
                            ->required()
                            ->default('available'),
                    ]),
                ]),

            Forms\Components\Section::make('Komposisi Resep (Smart Inventory & Auto-HPP)')
                ->description('Opsional: Tentukan takaran bahan baku per 1 porsi. Stok bahan akan otomatis berkurang saat transaksi di Kasir POS.')
                ->schema([
                    Forms\Components\Repeater::make('recipes')
                        ->relationship('recipes')
                        ->label('Daftar Bahan Baku')
                        ->schema([
                            Forms\Components\Select::make('ingredient_id')
                                ->label('Bahan Baku')
                                ->options(Ingredient::orderBy('name')->get()->mapWithKeys(fn ($i) => [
                                    $i->id => "{$i->name} ({$i->unit} • Rp " . number_format((float) $i->cost_per_unit, 0, ',', '.') . "/{$i->unit})",
                                ]))
                                ->required()
                                ->searchable()
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('quantity_used')
                                ->label('Takaran / Porsi')
                                ->numeric()
                                ->required()
                                ->minValue(0.001)
                                ->step(0.01)
                                ->default(0.1)
                                ->helperText('Sesuai satuan bahan (kg/liter/pcs)'),
                        ])
                        ->columns(3)
                        ->addActionLabel('Tambah Bahan Resep')
                        ->defaultItems(0)
                        ->collapsible(),
                ]),

            Forms\Components\Section::make('Foto Produk')
                ->schema([
                    Forms\Components\FileUpload::make('image')
                        ->label('Upload Foto Produk')
                        ->image()
                        ->disk('public')
                        ->directory('products')
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->defaultImageUrl(url('/images/no-image.png'))
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hpp')
                    ->label('HPP')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn ($record) => match (true) {
                        $record->stock <= 0  => 'danger',
                        $record->stock <= 5  => 'warning',
                        default              => 'success',
                    }),

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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(Category::pluck('name', 'id'))
                    ->placeholder('Semua Kategori'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'available'   => 'Tersedia',
                        'unavailable' => 'Tidak Tersedia',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis (≤ 5)')
                    ->query(fn (Builder $query) => $query->where('stock', '<=', 5)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada produk')
            ->emptyStateDescription('Tambahkan produk pertama Anda sekarang.')
            ->emptyStateIcon('heroicon-o-shopping-bag')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
