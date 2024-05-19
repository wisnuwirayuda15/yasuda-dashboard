<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use Filament\View\PanelsRenderHook;
use Filament\Livewire\Notifications;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Notification;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\ActionsPosition;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Enums\VerticalAlignment;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use App\Filament\Resources\ProfitLossResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
  public function panel(Panel $panel): Panel
  {
    // Register scroll to top event
    FilamentView::registerRenderHook(
      PanelsRenderHook::SCRIPTS_AFTER,
      fn(): string => new HtmlString('<script>document.addEventListener("scroll-to-top", () => window.scrollTo(0, 0))</script>'),
    );

    // Sending validation notifications
    if ((bool) env('VALIDATION_NOTIFICATION', true)) {
      Page::$reportValidationErrorUsing = function (ValidationException $exception) {
        Notification::make()
          ->title($exception->getMessage())
          ->danger()
          ->send();
        // ->sendToDatabase(auth()->user());
      };
    }

    // Global Settings
    Table::configureUsing(function (Table $table): void {
      $table
        ->paginationPageOptions([5, 10, 15, 20, 25, 30])
        ->extremePaginationLinks()
        ->actions([
        ], position: ActionsPosition::BeforeColumns);
    });
    Select::configureUsing(function (Select $select): void {
      $select
        ->preload()
        ->searchable()
        ->optionsLimit(5);
    });
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
      $switch
        ->locales(['en', 'id'])
        ->visible(outsidePanels: true);
    });

    // Notification alignment
    Notifications::alignment(Alignment::End);
    Notifications::verticalAlignment(VerticalAlignment::End);

    // Register Tailwind CSS CDN in local
    if (env('APP_ENV') === 'local' && env('TAILWIND_CDN') === true) {
      FilamentAsset::register([
        Js::make('tailwindcss', 'https://cdn.tailwindcss.com'),
      ]);
    }

    return $panel
      ->default()
      // ->spa()
      ->id('admin')
      ->path('')
      ->login(Login::class)
      ->passwordReset()
      ->font('Poppins')
      ->breadcrumbs(false)
      ->viteTheme('resources/css/filament/admin/theme.css')
      ->favicon(asset('favicon.svg'))
      ->brandLogo(asset('/img/logo-light.svg'))
      ->darkModeBrandLogo(asset('/img/logo-dark.svg'))
      ->brandLogoHeight('35px')
      ->sidebarCollapsibleOnDesktop(true)
      // ->darkMode(false)
      ->maxContentWidth(MaxWidth::Full)
      // ->databaseNotifications()
      // ->databaseNotificationsPolling('30s')
      ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
      ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
      ->pages([])
      ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
      ])
      // ->colors([
      //   'primary' => Color::Red,
      //   'secondary' => Color::Indigo,
      //   'danger' => Color::Red,
      //   'gray' => Color::Gray,
      //   'info' => Color::Sky,
      //   'success' => Color::Green,
      //   'warning' => Color::Yellow,
      // ])
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
      ->navigationGroups([
        NavigationGroup::make(NavigationGroupLabel::MASTER_DATA->value)
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
        QuickCreatePlugin::make()
          ->rounded(false)
          ->excludes([
            ProfitLossResource::class
          ]),
        SpotlightPlugin::make(),
      ]);
  }
}
