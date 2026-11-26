<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $product = $this->record->fresh(['recipes.ingredient']);
        if ($product && $product->recipes->isNotEmpty()) {
            $calculatedHpp = $product->recipes->sum(function ($recipe) {
                return (float) $recipe->quantity_used * (float) ($recipe->ingredient->cost_per_unit ?? 0);
            });
            $product->update(['hpp' => round($calculatedHpp, 0)]);
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Produk berhasil diperbarui';
    }
}
