<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class KasirPos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static string $view = 'filament.pages.kasir-pos';

    protected static ?string $navigationLabel = 'Kasir (Smart POS)';

    protected static ?string $title = 'Kasir Digital — Smart POS';

    protected static ?string $navigationGroup = 'Smart POS';

    protected static ?int $navigationSort = 1;

    public string $search = '';

    public ?int $selectedCategoryId = null;

    /** @var array<int, array{product_id:int, name:string, price:float, qty:int, max_stock:int}> */
    public array $cart = [];

    public string $payment_method = 'cash';

    public float $cash_received = 0;

    public ?int $lastTransactionId = null;

    public function addToCart(int $productId): void
    {
        $product = Product::where('status', 'available')->find($productId);
        if (! $product || $product->stock <= 0) {
            Notification::make()
                ->title('Stok produk habis')
                ->warning()
                ->send();
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] >= $product->stock) {
                Notification::make()
                    ->title('Melebihi stok tersedia')
                    ->warning()
                    ->send();
                return;
            }
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'price'      => (float) $product->price,
                'qty'        => 1,
                'max_stock'  => (int) $product->stock,
            ];
        }
    }

    public function incrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['qty'] < $this->cart[$productId]['max_stock']) {
            $this->cart[$productId]['qty']++;
        }
    }

    public function decrementQty(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['qty'] > 1) {
            $this->cart[$productId]['qty']--;
        } else {
            unset($this->cart[$productId]);
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->cash_received = 0;
    }

    public function getTotal(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['price'] * $item['qty']);
    }

    public function getChange(): float
    {
        if ($this->payment_method !== 'cash') {
            return 0;
        }

        return max(0, (float) $this->cash_received - $this->getTotal());
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang masih kosong')
                ->warning()
                ->send();
            return;
        }

        $total = $this->getTotal();
        if ($this->payment_method === 'cash' && $this->cash_received > 0 && $this->cash_received < $total) {
            Notification::make()
                ->title('Uang tunai kurang dari total belanja')
                ->danger()
                ->send();
            return;
        }

        $transaction = DB::transaction(function () use ($total) {
            $tx = Transaction::create([
                'transaction_code' => 'TRX-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8)),
                'total_price'      => $total,
                'payment_method'   => $this->payment_method,
                'status'           => 'completed',
                'transaction_date' => now(),
            ]);

            foreach ($this->cart as $item) {
                $product = Product::with('recipes.ingredient')->lockForUpdate()->find($item['product_id']);
                if (! $product) {
                    continue;
                }

                TransactionDetail::create([
                    'transaction_id' => $tx->id,
                    'product_id'     => $product->id,
                    'quantity'       => $item['qty'],
                    'price'          => $item['price'],
                    'subtotal'       => $item['price'] * $item['qty'],
                ]);

                // Kurangi stok produk
                $newStock = max(0, $product->stock - $item['qty']);
                $product->update([
                    'stock'  => $newStock,
                    'status' => $newStock <= 0 ? 'unavailable' : $product->status,
                ]);

                // Kurangi stok bahan baku sesuai resep (jika ada)
                foreach ($product->recipes as $recipe) {
                    if ($recipe->ingredient) {
                        $used = (float) $recipe->quantity_used * $item['qty'];
                        $recipe->ingredient->update([
                            'stock' => max(0, (float) $recipe->ingredient->stock - $used),
                        ]);
                    }
                }
            }

            return $tx;
        });

        $this->lastTransactionId = $transaction->id;
        $this->clearCart();

        Notification::make()
            ->title('Transaksi berhasil disimpan!')
            ->body("Kode: {$transaction->transaction_code} • Total: Rp " . number_format($total, 0, ',', '.'))
            ->success()
            ->send();
    }

    public function getCategories()
    {
        return Category::orderBy('name')->get();
    }

    public function getProducts()
    {
        return Product::with('category')
            ->where('status', 'available')
            ->where('stock', '>', 0)
            ->when($this->selectedCategoryId, fn ($q) => $q->where('category_id', $this->selectedCategoryId))
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->get();
    }

    public function getLastTransaction(): ?Transaction
    {
        if (! $this->lastTransactionId) {
            return null;
        }

        return Transaction::with('transactionDetails.product')->find($this->lastTransactionId);
    }
}
