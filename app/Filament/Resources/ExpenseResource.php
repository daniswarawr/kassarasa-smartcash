<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Biaya Operasional';

    protected static ?string $modelLabel = 'Biaya Operasional';

    protected static ?string $pluralModelLabel = 'Biaya Operasional';

    protected static ?string $navigationGroup = 'Keuangan & Laporan';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Auth::user()?->isOwner() ?? true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Catat Biaya Operasional')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Nama Pengeluaran')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Gas LPG 12kg, Listrik Bulanan, Gaji Kasir'),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'bahan_baku'  => 'Belanja Bahan Baku',
                                'listrik_air' => 'Listrik & Air',
                                'gas'         => 'Gas / Bahan Bakar',
                                'gaji'        => 'Gaji Karyawan',
                                'sewa'        => 'Sewa Tempat',
                                'lainnya'     => 'Lainnya',
                            ])
                            ->required()
                            ->default('lainnya'),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Nominal (Rp)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('Rp'),

                        Forms\Components\DatePicker::make('expense_date')
                            ->label('Tanggal Pengeluaran')
                            ->required()
                            ->default(now()),
                    ]),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Pengeluaran')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bahan_baku'  => 'Bahan Baku',
                        'listrik_air' => 'Listrik & Air',
                        'gas'         => 'Gas',
                        'gaji'        => 'Gaji',
                        'sewa'        => 'Sewa',
                        default       => 'Lainnya',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Nominal')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'bahan_baku'  => 'Bahan Baku',
                        'listrik_air' => 'Listrik & Air',
                        'gas'         => 'Gas',
                        'gaji'        => 'Gaji',
                        'sewa'        => 'Sewa',
                        'lainnya'     => 'Lainnya',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada catatan pengeluaran')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
