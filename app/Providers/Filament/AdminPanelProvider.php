<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\PanelProvider;
use App\Enums\CustomPlatform;
use App\Enums\JavascriptEvent;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use App\Filament\Resources\MeetingResource\Widgets\MeetingCalendarWidget;
use Filament\Tables\Filters\Filter;
use Filament\View\PanelsRenderHook;
use Filament\Livewire\Notifications;
use Filament\Support\Enums\MaxWidth;
use Filament\Actions\MountableAction;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use EightyNine\Approvals\ApprovalPlugin;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\ShirtResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Http\Middleware\Authenticate;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Enums\ActionsPosition;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Awcodes\FilamentVersions\VersionsPlugin;
use Awcodes\FilamentVersions\VersionsWidget;
use Filament\Forms\Components\DateTimePicker;
use Filament\Support\Enums\VerticalAlignment;
use App\Filament\Resources\ProfitLossResource;
use App\Filament\Resources\TourReportResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Filament\Tables\Actions\Action as TableAction;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Joaopaulolndev\FilamentEditEnv\FilamentEditEnvPlugin;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Actions\ActionGroup as TableActionGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Joaopaulolndev\FilamentCheckSslWidget\FilamentCheckSslWidgetPlugin;

class AdminPanelProvider extends PanelProvider
{
  public function boot(): void
  {
    // Register scroll to top event
    FilamentView::registerRenderHook(
      PanelsRenderHook::SCRIPTS_AFTER,
      function (): string {
        $event = JavascriptEvent::SCROLL_TO_TOP->value;

        $js = new HtmlString(<<<HTML
          <script>document.addEventListener('{$event}', () => window.scrollTo(0, 0))</script>
        HTML);

        return $js;
      }
    );

    // Sending validation notifications
    if ((bool) env('VALIDATION_NOTIFICATION', true)) {
      Page::$reportValidationErrorUsing = function (ValidationException $exception) {
        Notification::make()
          ->danger()
          ->title('Validation Error')
          ->body($exception->getMessage())
          ->send()
          // ->sendToDatabase(auth()->user())
        ;
      };
    }

    // Components global settings
    Table::configureUsing(function (Table $table): void {
      $table
        ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
        ->extremePaginationLinks()
        ->paginationPageOptions([5, 10, 15, 20])
        // ->filters(
        //   [
        //     Filter::make('approved')->approval(),
        //   ],
        //   FiltersLayout::AboveContentCollapsible
        // )
        ->persistFiltersInSession()
        ->deferFilters()
        ->filtersTriggerAction(
          fn(TableAction $action) => $action
            ->label('Filter')
            ->button()
        )
        ->filtersApplyAction(
          fn(TableAction $action) => $action
            ->label('Apply')
            ->color('success')
            ->icon('fas-check')
        )
        ->actions([
          TableActionGroup::make([
            TableViewAction::make(),
            TableEditAction::make(),
            TableDeleteAction::make(),
          ])->tooltip('Actions'),
        ], ActionsPosition::BeforeColumns);
    });

    Select::configureUsing(function (Select $select): void {
      $select
        ->preload()
        ->searchable()
        // ->optionsLimit(5)
      ;
    });

    Repeater::configureUsing(function (Repeater $repeater): void {
      $repeater
        ->expandAllAction(fn(FormAction $action) => $action
          ->label('Expand')
          ->icon('heroicon-s-chevron-down')
          ->color('primary'))
        ->collapseAllAction(fn(FormAction $action) => $action
          ->label('Collapse')
          ->icon('heroicon-s-chevron-up')
          ->color('secondary'));
    });

    DatePicker::configureUsing(function (DatePicker $datePicker): void {
      $datePicker
        ->native(false)
        // ->closeOnDateSelection()
        ->prefixIcon('heroicon-s-calendar-days')
        ->displayFormat('d mm Y');
    });

    DateTimePicker::configureUsing(function (DateTimePicker $dateTimePicker): void {
      $dateTimePicker
        ->native(false)
        ->prefixIcon('heroicon-s-calendar-days')
        // ->displayFormat('d mm Y • H:i')
      ;
    });

    ExportAction::configureUsing(function (ExportAction $action): void {
      $action
        ->icon('fileicon-microsoft-excel')
        ->tooltip('Export data to Excel');
    });

    MountableAction::configureUsing(fn(MountableAction $action) => $action->slideOver());

    TableDeleteAction::configureUsing(fn(TableDeleteAction $action) => $action->slideOver(false));

    LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
      $hook = match (CustomPlatform::detect()) {
        CustomPlatform::Windows, CustomPlatform::Mac, CustomPlatform::Linux => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        CustomPlatform::Mobile => PanelsRenderHook::SIDEBAR_NAV_END,
        default => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
      };

      $switch
        ->locales(['en', 'id'])
        ->visible(outsidePanels: true)
        ->circular()
        ->flags([
          'en' => asset('img/flags/en-circular.svg'),
          'id' => asset('img/flags/id-circular.svg'),
        ])
        ->renderHook($hook);
    });

    // Notification alignment
    // Notifications::alignment(Alignment::Center);
    // Notifications::verticalAlignment(VerticalAlignment::End);
  }

  public function panel(Panel $panel): Panel
  {
    $spa = env('SPA', false);

    return $panel
      ->default()
      ->id('admin')
      ->spa($spa)
      ->unsavedChangesAlerts(!$spa)
      ->path(env('APP_PATH', 'dashboard'))
      ->login(Login::class)
      ->passwordReset()
      ->emailVerification()
      ->requiresEmailVerification()
      ->font('Poppins')
      ->viteTheme('resources/css/filament/admin/theme.css')
      ->favicon(asset('favicon-white.svg'))
      ->brandLogo(asset('/img/logos/logo-light.svg'))
      ->darkModeBrandLogo(asset('/img/logos/logo-dark.svg'))
      ->brandLogoHeight('35px')
      ->sidebarCollapsibleOnDesktop()
      ->maxContentWidth(MaxWidth::Full)
      ->databaseNotifications()
      ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
      ->globalSearchFieldSuffix(fn(): ?string => match (CustomPlatform::detect()) {
        CustomPlatform::Windows, CustomPlatform::Linux => 'CTRL+K',
        CustomPlatform::Mac => '⌘K',
        CustomPlatform::Mobile => null,
        default => null,
      })
      ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
      ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
      ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
      ->pages([])
      ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
      ->widgets([
        // Widgets\AccountWidget::class,
        // Widgets\FilamentInfoWidget::class,
        VersionsWidget::class,
        MeetingCalendarWidget::class,
      ])
      ->colors([
        'primary' => Color::Rose,
        'secondary' => Color::Indigo,
        'danger' => Color::Red,
        'gray' => Color::Zinc,
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
      ->plugins([
        ApprovalPlugin::make(),
        FilamentProgressbarPlugin::make(),
        FilamentFullCalendarPlugin::make()
          ->selectable(true)
          ->editable(true),
        VersionsPlugin::make()
          ->hasNavigationView(false)
          ->widgetColumnSpan('full'),
        FilamentShieldPlugin::make()
          ->gridColumns(2)
          ->sectionColumnSpan(1)
          ->checkboxListColumns(2)
          ->resourceCheckboxListColumns(2),
        BreezyCore::make()
          ->enableTwoFactorAuthentication()
          ->avatarUploadComponent(fn() =>
            FileUpload::make('avatar_url')
              ->avatar()
              ->hiddenLabel()
              ->disk('profile')
              ->visible(fn(): bool => (bool) auth()->user()->employable))
          ->myProfile(
            // hasAvatars: true,
            shouldRegisterUserMenu: true,
            navigationGroup: NavigationGroupLabel::SETTING->getLabel(),
          ),
        QuickCreatePlugin::make()
          ->slideOver()
          ->sortBy('navigation')
          // ->rounded(fn(): bool => match (CustomPlatform::detect()) {
          //   CustomPlatform::Windows, CustomPlatform::Mac, CustomPlatform::Linux => true,
          //   CustomPlatform::Mobile => false,
          //   default => false,
          // })
          // ->label(fn(): ?string => match (CustomPlatform::detect()) {
          //   CustomPlatform::Windows, CustomPlatform::Mac, CustomPlatform::Linux => null,
          //   CustomPlatform::Mobile => 'Create',
          //   default => 'Create',
          // })
          // ->renderUsingHook(fn(): ?string => match (CustomPlatform::detect()) {
          //   CustomPlatform::Windows, CustomPlatform::Mac, CustomPlatform::Linux => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
          //   CustomPlatform::Mobile => PanelsRenderHook::SIDEBAR_NAV_START,
          //   default => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
          // })
          ->excludes([
            ProfitLossResource::class,
            TourReportResource::class,
            ShirtResource::class,
          ]),
      ]);
  }
}
