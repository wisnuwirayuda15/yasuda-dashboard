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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->sidebarCollapsibleOnDesktop(true)
      ->id('admin')
      ->path('admin')
      ->login()
      ->colors([
        'primary' => Color::Indigo,
        'success' => Color::Emerald,
        'warning' => Color::Orange,
        'danger' => Color::Rose,
        'gray' => Color::Gray,
        'info' => Color::Blue,
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
      ->plugin(
        BreezyCore::make()->myProfile(
          shouldRegisterUserMenu: true, // Sets the 'account' link in the panel User Menu (default = true)
          shouldRegisterNavigation: false, // Adds a main navigation item for the My Profile page (default = false)
          navigationGroup: 'Settings', // Sets the navigation group for the My Profile page (default = null)
          hasAvatars: true, // Enables the avatar upload form component (default = false)
          slug: 'profile' // Sets the slug for the profile page (default = 'my-profile')
        )
      );
  }
}
