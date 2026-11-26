<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak Struk')
                ->icon('heroicon-o-printer')
                ->color('primary')
                ->url(fn () => route('transactions.print', $this->record) . '?auto=1')
                ->openUrlInNewTab(),

            Actions\Action::make('whatsapp')
                ->label('Kirim WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->url(fn () => $this->record->whatsapp_url)
                ->openUrlInNewTab(),

            Actions\EditAction::make()->label('Edit'),
        ];
    }
}
