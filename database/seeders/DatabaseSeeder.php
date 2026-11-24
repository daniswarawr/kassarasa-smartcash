<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'admin@kasirdigital.com'],
            [
                'name'     => 'Admin Kassarasa (SmartCash)',
                'password' => Hash::make('admin123'),
            ]
        );

        // Categories
        $categories = [
            ['name' => 'Makanan Utama',   'description' => 'Menu makanan utama seperti nasi, mie, dan lauk.'],
            ['name' => 'Minuman',         'description' => 'Berbagai pilihan kopi, teh, dan minuman segar.'],
            ['name' => 'Snack & Camilan', 'description' => 'Camilan renyah dan makanan ringan pelengkap.'],
            ['name' => 'Dessert & Bakery','description' => 'Kue, croissant, toast, dan hidangan manis.'],
            ['name' => 'Paket Hemat',     'description' => 'Paket bundling makanan dan minuman.'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $catMakanan = Category::where('name', 'Makanan Utama')->first();
        $catMinuman = Category::where('name', 'Minuman')->first();
        $catSnack   = Category::where('name', 'Snack & Camilan')->first();
        $catDessert = Category::where('name', 'Dessert & Bakery')->first();
        $catPaket   = Category::where('name', 'Paket Hemat')->first();

        // Products (menu khas PPT Kassarasa / SmartCash + HPP + Gambar)
        $products = [
            ['category_id' => $catMakanan->id, 'name' => 'Nasi Ayam Geprek',     'image' => 'products/nasi-ayam-geprek.jpg',     'description' => 'Nasi hangat dengan ayam geprek crispy dan sambal korek.',      'price' => 25000, 'hpp' => 13500, 'stock' => 45, 'status' => 'available'],
            ['category_id' => $catMakanan->id, 'name' => 'Nasi Goreng Spesial',  'image' => 'products/nasi-goreng-spesial.jpg',  'description' => 'Nasi goreng dengan telur, ayam, dan sayuran segar.',          'price' => 25000, 'hpp' => 14000, 'stock' => 40, 'status' => 'available'],
            ['category_id' => $catMakanan->id, 'name' => 'Mie Goreng Jawa',      'image' => 'products/mie-goreng-jawa.jpg',      'description' => 'Mie goreng khas Jawa dengan bumbu rempah pilihan.',            'price' => 22000, 'hpp' => 11500, 'stock' => 35, 'status' => 'available'],
            ['category_id' => $catMakanan->id, 'name' => 'Gado-Gado',            'image' => 'products/gado-gado.jpg',            'description' => 'Sayuran segar dengan saus kacang spesial.',                   'price' => 15000, 'hpp' => 9000,  'stock' => 3,  'status' => 'available'],
            ['category_id' => $catMinuman->id, 'name' => 'Es Kopi Susu',         'image' => 'products/es-kopi-susu.jpg',         'description' => 'Espresso arabika dengan susu segar dan gula aren.',           'price' => 18000, 'hpp' => 8500,  'stock' => 60, 'status' => 'available'],
            ['category_id' => $catMinuman->id, 'name' => 'Matcha Latte Blend',   'image' => 'products/matcha-latte-blend.jpg',   'description' => 'Matcha Jepang premium dengan susu creamy.',                   'price' => 22000, 'hpp' => 11000, 'stock' => 30, 'status' => 'available'],
            ['category_id' => $catMinuman->id, 'name' => 'Es Teh Manis',         'image' => 'products/es-teh-manis.jpg',         'description' => 'Teh segar dengan es dan gula aren.',                          'price' => 6000,  'hpp' => 2500,  'stock' => 100,'status' => 'available'],
            ['category_id' => $catMinuman->id, 'name' => 'Kopi Hitam',           'image' => 'products/kopi-hitam.jpg',           'description' => 'Kopi hitam pekat tanpa gula.',                               'price' => 10000, 'hpp' => 4000,  'stock' => 2,  'status' => 'available'],
            ['category_id' => $catSnack->id,   'name' => 'Cireng Isi',           'image' => 'products/cireng-isi.jpg',           'description' => 'Cireng renyah isi ayam suwir pedas.',                         'price' => 15000, 'hpp' => 7500,  'stock' => 40, 'status' => 'available'],
            ['category_id' => $catSnack->id,   'name' => 'Pisang Goreng Crispy', 'image' => 'products/pisang-goreng-crispy.jpg', 'description' => 'Pisang goreng renyah dengan topping coklat atau keju.',       'price' => 12000, 'hpp' => 6000,  'stock' => 25, 'status' => 'available'],
            ['category_id' => $catDessert->id, 'name' => 'Croissant Butter',     'image' => 'products/croissant-butter.jpg',     'description' => 'Croissant renyah berlapis mentega premium.',                  'price' => 20000, 'hpp' => 10500, 'stock' => 12, 'status' => 'available'],
            ['category_id' => $catDessert->id, 'name' => 'Toast Sarang Madu',    'image' => 'products/toast-sarang-madu.jpg',    'description' => 'Roti panggang tebal dengan madu dan mentega.',                'price' => 24000, 'hpp' => 12000, 'stock' => 18, 'status' => 'available'],
            ['category_id' => $catPaket->id,   'name' => 'Paket Geprek + Es Teh','image' => 'products/paket-geprek-es-teh.jpg',  'description' => 'Nasi Ayam Geprek + Es Teh Manis hemat.',                     'price' => 28000, 'hpp' => 16000, 'stock' => 0,  'status' => 'unavailable'],
        ];

        foreach ($products as $prod) {
            Product::updateOrCreate(['name' => $prod['name']], $prod);
        }

        // Smart Inventory: Bahan Baku (sesuai mockup Slide 1 & Slide 4 PPT)
        $ingredients = [
            ['name' => 'Ayam Fillet',    'unit' => 'kg',   'stock' => 2.0,  'min_stock' => 3.0,  'cost_per_unit' => 42000],
            ['name' => 'Cabai Rawit',    'unit' => 'kg',   'stock' => 0.5,  'min_stock' => 1.0,  'cost_per_unit' => 60000],
            ['name' => 'Minyak Goreng',  'unit' => 'liter','stock' => 3.0,  'min_stock' => 4.0,  'cost_per_unit' => 18000],
            ['name' => 'Telur Ayam',     'unit' => 'kg',   'stock' => 1.5,  'min_stock' => 2.0,  'cost_per_unit' => 28000],
            ['name' => 'Beras Premium',  'unit' => 'kg',   'stock' => 25.0, 'min_stock' => 5.0,  'cost_per_unit' => 14000],
            ['name' => 'Bumbu Rempah',   'unit' => 'pack', 'stock' => 18.0, 'min_stock' => 5.0,  'cost_per_unit' => 2000],
            ['name' => 'Kemasan Box',    'unit' => 'pcs',  'stock' => 150,  'min_stock' => 30,   'cost_per_unit' => 1000],
            ['name' => 'Kopi Espresso',  'unit' => 'kg',   'stock' => 4.5,  'min_stock' => 1.0,  'cost_per_unit' => 180000],
            ['name' => 'Susu UHT',       'unit' => 'liter','stock' => 12.0, 'min_stock' => 3.0,  'cost_per_unit' => 19000],
            ['name' => 'Gula Aren Cair', 'unit' => 'liter','stock' => 6.0,  'min_stock' => 1.5,  'cost_per_unit' => 25000],
            ['name' => 'Tepung Tapioka', 'unit' => 'kg',   'stock' => 0.0,  'min_stock' => 2.0,  'cost_per_unit' => 14000],
        ];

        foreach ($ingredients as $ing) {
            Ingredient::updateOrCreate(['name' => $ing['name']], $ing);
        }

        // Resep Produk untuk HPP Calculator & Pengurangan Stok Bahan Otomatis
        $nasiGeprek = Product::where('name', 'Nasi Ayam Geprek')->first();
        $ayam       = Ingredient::where('name', 'Ayam Fillet')->first();
        $beras      = Ingredient::where('name', 'Beras Premium')->first();
        $cabai      = Ingredient::where('name', 'Cabai Rawit')->first();
        $kemasan    = Ingredient::where('name', 'Kemasan Box')->first();

        if ($nasiGeprek && $ayam && $beras && $cabai && $kemasan && ProductRecipe::count() === 0) {
            ProductRecipe::create(['product_id' => $nasiGeprek->id, 'ingredient_id' => $beras->id,   'quantity_used' => 0.150]);
            ProductRecipe::create(['product_id' => $nasiGeprek->id, 'ingredient_id' => $ayam->id,    'quantity_used' => 0.120]);
            ProductRecipe::create(['product_id' => $nasiGeprek->id, 'ingredient_id' => $cabai->id,   'quantity_used' => 0.030]);
            ProductRecipe::create(['product_id' => $nasiGeprek->id, 'ingredient_id' => $kemasan->id, 'quantity_used' => 1.000]);
        }

        $kopiSusu = Product::where('name', 'Es Kopi Susu')->first();
        $espresso = Ingredient::where('name', 'Kopi Espresso')->first();
        $susu     = Ingredient::where('name', 'Susu UHT')->first();
        $gulaAren = Ingredient::where('name', 'Gula Aren Cair')->first();

        if ($kopiSusu && $espresso && $susu && $gulaAren) {
            ProductRecipe::firstOrCreate(['product_id' => $kopiSusu->id, 'ingredient_id' => $espresso->id], ['quantity_used' => 0.020]);
            ProductRecipe::firstOrCreate(['product_id' => $kopiSusu->id, 'ingredient_id' => $susu->id],     ['quantity_used' => 0.150]);
            ProductRecipe::firstOrCreate(['product_id' => $kopiSusu->id, 'ingredient_id' => $gulaAren->id], ['quantity_used' => 0.030]);
        }

        // Transactions & details (sample data for the last 30 days)
        if (Transaction::count() === 0) {
            $productList = Product::where('status', 'available')->get();
            $methods = ['cash', 'transfer', 'qris', 'debit'];

            for ($i = 29; $i >= 0; $i--) {
                $txCount = rand(3, 10);
                for ($t = 0; $t < $txCount; $t++) {
                    $totalPrice = 0;
                    $items = $productList->random(rand(1, 4));
                    $detailData = [];

                    foreach ($items as $product) {
                        $qty      = rand(1, 3);
                        $price    = $product->price;
                        $subtotal = $price * $qty;
                        $totalPrice += $subtotal;

                        $detailData[] = [
                            'product_id' => $product->id,
                            'quantity'   => $qty,
                            'price'      => $price,
                            'subtotal'   => $subtotal,
                        ];
                    }

                    $transaction = Transaction::create([
                        'transaction_code' => 'TRX-' . strtoupper(substr(md5(uniqid()), 0, 8)),
                        'total_price'      => $totalPrice,
                        'payment_method'   => $methods[array_rand($methods)],
                        'status'           => 'completed',
                        'transaction_date' => now()->subDays($i)->setTime(rand(8, 21), rand(0, 59)),
                        'created_at'       => now()->subDays($i),
                        'updated_at'       => now()->subDays($i),
                    ]);

                    foreach ($detailData as $detail) {
                        TransactionDetail::create(array_merge($detail, [
                            'transaction_id' => $transaction->id,
                        ]));
                    }
                }
            }
        }
    }
}
