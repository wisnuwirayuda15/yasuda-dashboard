<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\PanelProvider;
use App\Enums\CustomPlatform;
use App\Enums\JavascriptEvent;
use App\Filament\Pages\Dashboard;
use App\Settings\GeneralSettings;
use App\Filament\Pages\Auth\Login;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Filament\Livewire\Notifications;
use Filament\Support\Enums\MaxWidth;
use App\Filament\Widgets\FleetWidget;
use Filament\Actions\MountableAction;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Repeater;
use Filament\Navigation\NavigationItem;
use Filament\Tables\Columns\TextColumn;
use EightyNine\Approvals\ApprovalPlugin;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Enums\FiltersLayout;
use App\Filament\Resources\ShirtResource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Widgets\ProfitLossWidget;
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
use Filament\FontProviders\GoogleFontProvider;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Resources\LoyaltyPointResource;
use Filament\Actions\Exports\Enums\ExportFormat;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Filament\Tables\Actions\Action as TableAction;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use GeoSot\FilamentEnvEditor\FilamentEnvEditorPlugin;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Http\Middleware\DisableBladeIconComponents;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Tables\Actions\EditAction as TableEditAction;
use Filament\Tables\Actions\ViewAction as TableViewAction;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Njxqlus\FilamentProgressbar\FilamentProgressbarPlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Tables\Actions\ActionGroup as TableActionGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use Filament\Tables\Actions\DeleteAction as TableDeleteAction;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use App\Filament\Resources\MeetingResource\Widgets\MeetingCalendarWidget;
use App\Filament\Resources\OrderFleetResource\Widgets\OrderFleetCalendarWidget;

class AdminPanelProvider extends PanelProvider
{
  public function boot(): void
  {
    // Register scroll to top event
    FilamentView::registerRenderHook(
      PanelsRenderHook::SCRIPTS_AFTER,
      function (): HtmlString {
        $event = JavascriptEvent::SCROLL_TO_TOP->value;

        $js = new HtmlString(<<<HTML
          <script>document.addEventListener('{$event}', () => window.scrollTo(0, 0))</script>
        HTML);

        return $js;
      }
    );

    // Register roles name in navbar
    FilamentView::registerRenderHook(
      PanelsRenderHook::GLOBAL_SEARCH_AFTER,
      fn(): View => view('filament.components.badges.user-role'),
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
        ->striped(fn(GeneralSettings $settings) => $settings->table_striped)
        ->modifyQueryUsing(fn(Builder $query) => $query->withoutGlobalScopes())
        ->extremePaginationLinks()
        ->defaultSort('created_at', 'desc')
        ->paginationPageOptions([5, 10, 15, 20])
        ->persistFiltersInSession()
        ->persistSortInSession()
        ->deferFilters()
        ->selectCurrentPageOnly()
        ->filtersTriggerAction(fn(TableAction $action) => $action
          ->label('Filter')
          ->button())
        ->filtersApplyAction(fn(TableAction $action) => $action
          ->label('Apply')
          ->color('success')
          ->icon('fas-check'))
        ->actions(
          [
            TableActionGroup::make([
              TableViewAction::make(),
              TableEditAction::make(),
              TableDeleteAction::make(),
            ])
          ],
          fn(GeneralSettings $settings) => match ($settings->table_actionPosition) {
            ActionsPosition::AfterCells->name => ActionsPosition::AfterCells,
            ActionsPosition::AfterColumns->name => ActionsPosition::AfterColumns,
            ActionsPosition::AfterContent->name => ActionsPosition::AfterContent,
            ActionsPosition::BeforeCells->name => ActionsPosition::BeforeCells,
            ActionsPosition::BeforeColumns->name => ActionsPosition::BeforeColumns,
            default => ActionsPosition::BeforeCells
          }
        );
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
        // ->native(false)
        ->prefixIcon('heroicon-s-calendar-days')
        // ->displayFormat('d mm Y • H:i')
      ;
    });

    ExportAction::configureUsing(function (ExportAction $action): void {
      $action
        ->icon('fas-file-export')
        ->tooltip('Export data to csv file')
        ->formats([ExportFormat::Csv]);
    });

    ImportAction::configureUsing(function (ImportAction $action): void {
      $action
        ->icon('fas-file-import')
        ->tooltip('Import data from csv file');
    });

    ApprovalStatusColumn::configureUsing(function (ApprovalStatusColumn $column) {
      $column
        ->label('Approval Status')
        ->sortable()
        ->toggleable();
    });

    ImageColumn::configureUsing(function (ImageColumn $column) {
      $column
        ->alignCenter()
        ->height(50)
        ->width('auto')
        ->defaultImageUrl(asset('img/placeholder/no-image.jpg'))
        ->extraImgAttributes(fn(?string $state) => [
          'class' => 'rounded',
          'loading' => 'lazy',
          'title' => blank($state) ? 'No image' : null
        ], true);
    });

    MountableAction::configureUsing(function (MountableAction $action) {
      $action->slideOver();
    });

    // TableDeleteAction::configureUsing(fn(TableDeleteAction $action) => $action->slideOver(false));

    LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
      $hook = match (CustomPlatform::detect()) {
        CustomPlatform::Windows, CustomPlatform::Mac, CustomPlatform::Linux => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
        CustomPlatform::Mobile => PanelsRenderHook::SIDEBAR_NAV_END,
        default => PanelsRenderHook::GLOBAL_SEARCH_AFTER,
      };

      $switch
        ->circular()
        ->locales(['en', 'id'])
        ->visible(outsidePanels: true)
        ->renderHook($hook)
        ->flags([
          'en' => asset('img/flags/en-circular.svg'),
          'id' => asset('img/flags/id-circular.svg'),
        ]);
    });

    // Notification alignment
    // Notifications::alignment(Alignment::Center);
    // Notifications::verticalAlignment(VerticalAlignment::End);
  }

  public function panel(Panel $panel): Panel
  {
    $settings = new GeneralSettings();

    return $panel
      ->default()
      ->id('admin')
      ->topNavigation(fn(GeneralSettings $settings) => $settings->site_navigation)
      ->spa(fn(GeneralSettings $settings) => $settings->site_spa)
      // ->unsavedChangesAlerts(fn(GeneralSettings $settings) => !$settings->site_spa)
      ->path(env('APP_PATH', 'dashboard'))
      ->login(Login::class)
      ->passwordReset()
      ->emailVerification()
      ->requiresEmailVerification()
      ->font($settings->site_font, provider: GoogleFontProvider::class)
      ->viteTheme('resources/css/filament/admin/theme.css')
      ->favicon(asset('favicon-white.svg'))
      ->brandName(fn(GeneralSettings $settings) => $settings->site_name)
      ->brandLogo(asset('/img/logos/logo-light.svg'))
      ->darkModeBrandLogo(asset('/img/logos/logo-dark.svg'))
      ->brandLogoHeight(fn(GeneralSettings $settings) => $settings->site_logoHeight . 'px')
      ->sidebarCollapsibleOnDesktop()
      ->maxContentWidth($settings->site_maxContentWidth)
      ->databaseNotifications()
      ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
      ->globalSearchFieldSuffix(fn(): ?string => match (CustomPlatform::detect()) {
        CustomPlatform::Windows, CustomPlatform::Linux => 'CTRL+K',
        CustomPlatform::Mac => '⌘K',
        CustomPlatform::Mobile => null,
        default => null,
      })
      ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
      ->pages([])
      ->discoverResources(app_path('Filament/Resources'), 'App\\Filament\\Resources')
      ->discoverPages(app_path('Filament/Pages'), 'App\\Filament\\Pages')
      ->discoverWidgets(app_path('Filament/Widgets'), 'App\\Filament\\Widgets')
      ->navigationItems([
        NavigationItem::make(__('navigation.label.pulse'))
          ->visible(fn() => config('pulse.enabled') && auth()->user()->isSuperAdmin())
          ->group(NavigationGroupLabel::SYSTEM->getLabel())
          ->url('/' . config('pulse.path'), true)
          ->icon('gmdi-graphic-eq-r')
          ->sort(9),
      ])
      ->widgets([
        Widgets\AccountWidget::class,
          // Widgets\FilamentInfoWidget::class,
          // VersionsWidget::class,
        OrderFleetCalendarWidget::class,
        MeetingCalendarWidget::class,
      ])
      ->resources([
        config('filament-logger.activity_resource')
      ])
      ->colors(fn(GeneralSettings $settings) => [
        'primary' => Color::rgb("rgb({$settings->color_primary['value']})"),
        'secondary' => Color::rgb("rgb({$settings->color_secondary['value']})"),
        'gray' => Color::Zinc,
        'danger' => Color::Red,
        'info' => Color::Sky,
        'success' => Color::Green,
        'warning' => Color::Yellow,
      ])
      // ->colors([
      //   'primary' => Color::hex('#d82431'),
      //   'secondary' => Color::hex('#64266e'),
      //   'gray' => Color::Zinc,
      //   'danger' => Color::Red,
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
      ->plugins([
        ApprovalPlugin::make(),
        // GlobalSearchModalPlugin::make(),
        FilamentProgressbarPlugin::make(),
        FilamentEnvEditorPlugin::make()
          ->authorize(fn() => auth()->user()->isSuperAdmin())
          ->navigationGroup(NavigationGroupLabel::SETTING->getLabel())
          ->navigationIcon('eos-configuration-file'),
        FilamentFullCalendarPlugin::make()
          ->selectable(true)
          ->editable(true),
        VersionsPlugin::make()
          ->hasNavigationView(fn() => app()->environment('local'))
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
            hasAvatars: false,
            shouldRegisterUserMenu: true,
            navigationGroup: NavigationGroupLabel::SETTING->getLabel(),
          ),
        QuickCreatePlugin::make()
          ->slideOver()
          ->sortBy('navigation')
          ->hidden(fn() => !auth()->user()->hasVerifiedEmail())
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
            LoyaltyPointResource::class,
            ShirtResource::class,
            config('filament-logger.activity_resource')
          ]),
      ]);
  }
}
