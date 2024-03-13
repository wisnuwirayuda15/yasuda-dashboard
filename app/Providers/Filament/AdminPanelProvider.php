<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use SolutionForest\FilamentSimpleLightBox\SimpleLightBoxPlugin;

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
        'primary' => Color::Sky,
        // 'success' => Color::Emerald,
        // 'warning' => Color::Orange,
        // 'danger' => Color::Red,
        // 'gray' => Color::Gray,
        // 'info' => Color::Blue,
      ])
      ->font('Poppins')
      ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
      ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
      ->pages([
        Pages\Dashboard::class,
      ])
      ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
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
      ])
      ->plugins([
        BreezyCore::make()
          ->avatarUploadComponent(fn($fileUpload) => $fileUpload->disableLabel())
          ->myProfile(
            shouldRegisterUserMenu: true,
            shouldRegisterNavigation: false,
            navigationGroup: 'Settings',
            hasAvatars: true,
            slug: 'profile'
          )
          ->enableTwoFactorAuthentication(),
        FilamentProgressbarPlugin::make()->color('#0096c7'),
        SimpleLightBoxPlugin::make(),
      ])
      ->sidebarCollapsibleOnDesktop(true)
      ->databaseNotifications()
      ->databaseNotificationsPolling('30s');
  }
}
