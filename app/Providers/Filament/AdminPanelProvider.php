<?php

namespace App\Providers\Filament;

use App\Filament\Pages\BusinessAssistant;
use App\Filament\Pages\HppCalculator;
use App\Filament\Pages\KasirPos;
use App\Filament\Pages\KitchenDisplay;
use App\Filament\Pages\LaporanPenjualan;
use App\Filament\Pages\ShiftKasir;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ExpenseResource;
use App\Filament\Resources\IngredientResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\LatestTransactionsWidget;
use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\SalesChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::hex('#0E4D42'),
                'success' => Color::hex('#65A30D'),
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
                'info'    => Color::Teal,
            ])
            ->brandName('SMARTCASH • KASSARASA')
            ->brandLogo(fn () => view('filament.brand-logo'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('images/logo-kassarasa.jpg'))
            ->darkMode(false)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Smart POS')
                    ->icon('heroicon-o-computer-desktop'),
                NavigationGroup::make('Produk & Smart Inventory')
                    ->icon('heroicon-o-cube'),
                NavigationGroup::make('Keuangan & Laporan')
                    ->icon('heroicon-o-chart-bar'),
            ])
            ->resources([
                ProductResource::class,
                CategoryResource::class,
                IngredientResource::class,
                TransactionResource::class,
                ExpenseResource::class,
                UserResource::class,
            ])
            ->pages([
                Dashboard::class,
                KasirPos::class,
                KitchenDisplay::class,
                ShiftKasir::class,
                HppCalculator::class,
                LaporanPenjualan::class,
                BusinessAssistant::class,
            ])
            ->widgets([
                StatsOverviewWidget::class,
                SalesChartWidget::class,
                LatestTransactionsWidget::class,
                LowStockProductsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
