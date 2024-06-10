<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\PanelProvider;
use Filament\Support\Assets\Js;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use Filament\View\PanelsRenderHook;
use Filament\Livewire\Notifications;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\Platform;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Navigation\NavigationGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Resources\FleetResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ShirtResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationBuilder;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\ActionsPosition;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use App\Filament\Resources\InvoiceResource;
use App\Filament\Resources\MeetingResource;
use Filament\Support\Facades\FilamentAsset;
use App\Filament\Resources\CustomerResource;
use Awcodes\FilamentVersions\VersionsPlugin;
use Awcodes\FilamentVersions\VersionsWidget;
use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Enums\VerticalAlignment;
use App\Filament\Resources\OrderFleetResource;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\TourLeaderResource;
use App\Filament\Resources\TourReportResource;
use Illuminate\Validation\ValidationException;
use App\Filament\Resources\DestinationResource;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\TourTemplateResource;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
  public function boot(): void
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
        ->extremePaginationLinks()
        ->paginationPageOptions([5, 10, 15, 20, 25, 30])
        ->actions([
          ActionGroup::make([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
          ]),
        ], ActionsPosition::BeforeColumns);
    });
    Select::configureUsing(function (Select $select): void {
      $select
        ->preload()
        ->searchable()
        // ->optionsLimit(5)
      ;
    });
    DatePicker::configureUsing(function (DatePicker $datePicker): void {
      $datePicker
        ->native(false)
        ->closeOnDateSelection()
        ->prefixIcon('heroicon-s-calendar-days')
        ->displayFormat('d mm Y');
    });
    DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
      $dateTimePicker
        ->prefixIcon('heroicon-s-calendar-days')
        ->displayFormat('d mm Y • H:i');
    });
    LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
      $switch
        ->locales(['en', 'id'])
        ->visible(outsidePanels: true);
    });

    // Notification alignment
    Notifications::alignment(Alignment::Center);
    // Notifications::verticalAlignment(VerticalAlignment::End);

    // Register Tailwind CSS CDN in local
    if (env('APP_ENV') === 'local' && env('TAILWIND_CDN') === true) {
      FilamentAsset::register([
        Js::make('tailwindcss', 'https://cdn.tailwindcss.com'),
      ]);
    }
  }

  public function panel(Panel $panel): Panel
  {
    return $panel
      ->default()
      ->spa()
      ->id('admin')
      ->path('dashboard')
      ->login(Login::class)
      ->passwordReset()
      ->font('Poppins')
      ->viteTheme('resources/css/filament/admin/theme.css')
      ->favicon(asset('favicon.svg'))
      ->brandLogo(asset('/img/logo-light.svg'))
      ->darkModeBrandLogo(asset('/img/logo-dark.svg'))
      ->brandLogoHeight('35px')
      ->sidebarCollapsibleOnDesktop(true)
      ->maxContentWidth(MaxWidth::Full)
      ->databaseNotifications()
      ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
      ->globalSearchFieldSuffix(fn(): ?string => match (Platform::detect()) {
        Platform::Windows, Platform::Linux => 'CTRL+K',
        Platform::Mac => '⌘K',
        default => null,
      })
      ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
      ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
      ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
      ->pages([])
      ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
      ->widgets([
        Widgets\AccountWidget::class,
        Widgets\FilamentInfoWidget::class,
        VersionsWidget::class,
      ])
      ->colors([
        'primary' => Color::Rose,
        'secondary' => Color::Indigo,
        'danger' => Color::Red,
        'gray' => Color::Gray,
        'info' => Color::Sky,
        'success' => Color::Green,
        'warning' => Color::Yellow,
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
      ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
        return $builder
          ->items([
            ...Dashboard::getNavigationItems(),
          ])
          ->groups([
            NavigationGroup::make()
              ->label(NavigationGroupLabel::MASTER_DATA->getLabel())
              ->items([
                ...FleetResource::getNavigationItems(),
                ...CustomerResource::getNavigationItems(),
                ...DestinationResource::getNavigationItems(),
                ...TourLeaderResource::getNavigationItems(),
                ...TourTemplateResource::getNavigationItems(),
              ]),
            NavigationGroup::make()
              ->label(NavigationGroupLabel::OPERATIONAL->getLabel())
              ->items([
                ...OrderResource::getNavigationItems(),
                ...OrderFleetResource::getNavigationItems(),
                ...ShirtResource::getNavigationItems(),
                ...MeetingResource::getNavigationItems(),
              ]),
            NavigationGroup::make()
              ->label(NavigationGroupLabel::FINANCE->getLabel())
              ->items([
                ...InvoiceResource::getNavigationItems(),
                ...ProfitLossResource::getNavigationItems(),
                ...TourReportResource::getNavigationItems(),
              ]),
          ]);
      })
      ->plugins([
        VersionsPlugin::make()
          ->hasNavigationView(false)
          ->widgetColumnSpan('full'),
        BreezyCore::make()
          ->avatarUploadComponent(fn(FileUpload $fileUpload) => $fileUpload->hiddenLabel())
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
            ProfitLossResource::class,
            TourReportResource::class,
          ]),
        FilamentFullCalendarPlugin::make()
          ->selectable(true)
          ->editable(true),
      ]);
  }
}
