<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';

    protected static ?string $navigationLabel = 'Riwayat Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';

    protected static ?string $navigationGroup = 'Smart POS';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Transaksi')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('transaction_code')
                            ->label('Kode Transaksi')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\DateTimePicker::make('transaction_date')
                            ->label('Tanggal Transaksi')
                            ->required()
                            ->default(now()),
                    ]),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Harga (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash'     => 'Tunai (Cash)',
                                'transfer' => 'Transfer Bank',
                                'qris'     => 'QRIS',
                                'debit'    => 'Kartu Debit',
                                'credit'   => 'Kartu Kredit',
                            ])
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending'   => 'Menunggu',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                            ])
                            ->required()
                            ->default('completed'),
                    ]),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail Transaksi')
                ->schema([
                    Infolists\Components\Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('transaction_code')
                            ->label('Kode Transaksi')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('transaction_date')
                            ->label('Tanggal')
                            ->dateTime('d M Y, H:i'),

                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'completed' => 'success',
                                'pending'   => 'warning',
                                'cancelled' => 'danger',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'completed' => 'Selesai',
                                'pending'   => 'Menunggu',
                                'cancelled' => 'Dibatalkan',
                                default     => $state,
                            }),
                    ]),

                    Infolists\Components\Grid::make(2)->schema([
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Total Pembayaran')
                            ->money('IDR')
                            ->weight('bold')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'cash'     => 'Tunai (Cash)',
                                'transfer' => 'Transfer Bank',
                                'qris'     => 'QRIS',
                                'debit'    => 'Kartu Debit',
                                'credit'   => 'Kartu Kredit',
                                default    => $state,
                            }),
                    ]),
                ]),

            Infolists\Components\Section::make('Detail Produk yang Dibeli')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('transactionDetails')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('product.name')
                                ->label('Produk'),
                            Infolists\Components\TextEntry::make('quantity')
                                ->label('Jumlah'),
                            Infolists\Components\TextEntry::make('price')
                                ->label('Harga Satuan')
                                ->money('IDR'),
                            Infolists\Components\TextEntry::make('subtotal')
                                ->label('Subtotal')
                                ->money('IDR')
                                ->weight('bold'),
                        ])
                        ->columns(4),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->label('Kode Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'     => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris'     => 'QRIS',
                        'debit'    => 'Debit',
                        'credit'   => 'Kredit',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'pending'   => 'Menunggu',
                        'cancelled' => 'Dibatalkan',
                        default     => $state,
                    }),

                Tables\Columns\TextColumn::make('transactionDetails_count')
                    ->label('Item')
                    ->counts('transactionDetails')
                    ->badge()
                    ->color('gray'),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'pending'   => 'Menunggu',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash'     => 'Tunai',
                        'transfer' => 'Transfer',
                        'qris'     => 'QRIS',
                        'debit'    => 'Debit',
                        'credit'   => 'Kredit',
                    ])
                    ->placeholder('Semua Metode'),

                Tables\Filters\Filter::make('transaction_date')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn ($q) => $q->whereDate('transaction_date', '>=', $data['dari_tanggal']))
                            ->when($data['sampai_tanggal'], fn ($q) => $q->whereDate('transaction_date', '<=', $data['sampai_tanggal']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Detail'),
                Tables\Actions\EditAction::make()->label('Edit'),
                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus Terpilih'),
                ]),
            ])
            ->emptyStateHeading('Belum ada transaksi')
            ->emptyStateIcon('heroicon-o-receipt-percent')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'view'   => Pages\ViewTransaction::route('/{record}'),
            'edit'   => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
