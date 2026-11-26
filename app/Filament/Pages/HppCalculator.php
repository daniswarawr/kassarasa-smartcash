<?php

namespace App\Filament\Pages;

use App\Models\Ingredient;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class HppCalculator extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $view = 'filament.pages.hpp-calculator';

    protected static ?string $navigationLabel = 'HPP Calculator';

    protected static ?string $title = 'HPP Calculator & Simulasi Laba';

    protected static ?string $navigationGroup = 'Keuangan & Laporan';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()?->isOwner() ?? true;
    }

    public ?int $selected_product_id = null;

    public array $items = [];

    public float $selling_price = 15000;

    public int $simulation_portions = 20;

    public function mount(): void
    {
        $this->items = [
            ['name' => 'Beras', 'cost' => 2000],
            ['name' => 'Ayam', 'cost' => 4000],
            ['name' => 'Bumbu', 'cost' => 2000],
            ['name' => 'Kemasan', 'cost' => 1000],
        ];
    }

    public function updatedSelectedProductId($value): void
    {
        if (! $value) {
            return;
        }

        $product = Product::with('recipes.ingredient')->find($value);
        if (! $product) {
            return;
        }

        $this->selling_price = (float) $product->price;

        if ($product->recipes->isNotEmpty()) {
            $this->items = $product->recipes->map(function ($recipe) {
                $cost = (float) $recipe->quantity_used * (float) ($recipe->ingredient->cost_per_unit ?? 0);
                return [
                    'name' => $recipe->ingredient->name . ' (' . (float) $recipe->quantity_used . ' ' . $recipe->ingredient->unit . ')',
                    'cost' => round($cost, 0),
                ];
            })->toArray();
        } elseif ((float) $product->hpp > 0) {
            $this->items = [
                ['name' => 'Bahan Baku & Kemasan (Estimasi)', 'cost' => (float) $product->hpp],
            ];
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['name' => '', 'cost' => 0];
    }

    public function addFromIngredient(int $ingredientId): void
    {
        $ingredient = Ingredient::find($ingredientId);
        if (! $ingredient) {
            return;
        }

        $this->items[] = [
            'name' => $ingredient->name . ' (1 ' . $ingredient->unit . ')',
            'cost' => (float) $ingredient->cost_per_unit,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function getHppPerPortion(): float
    {
        return collect($this->items)->sum(fn ($item) => (float) ($item['cost'] ?? 0));
    }

    public function getGrossProfitPerPortion(): float
    {
        return (float) $this->selling_price - $this->getHppPerPortion();
    }

    public function getMarginPercent(): float
    {
        if ((float) $this->selling_price <= 0) {
            return 0;
        }

        return round(($this->getGrossProfitPerPortion() / (float) $this->selling_price) * 100, 1);
    }

    public function saveToProduct(): void
    {
        if (! $this->selected_product_id) {
            Notification::make()
                ->title('Pilih produk terlebih dahulu')
                ->warning()
                ->send();
            return;
        }

        $product = Product::find($this->selected_product_id);
        if (! $product) {
            return;
        }

        $hpp = $this->getHppPerPortion();
        $product->update([
            'hpp'   => $hpp,
            'price' => $this->selling_price,
        ]);

        Notification::make()
            ->title('HPP berhasil disimpan!')
            ->body("HPP {$product->name} diperbarui menjadi Rp " . number_format($hpp, 0, ',', '.'))
            ->success()
            ->send();
    }

    public function getProducts()
    {
        return Product::orderBy('name')->get();
    }

    public function getIngredients()
    {
        return Ingredient::orderBy('name')->get();
    }
}
