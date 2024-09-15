<?php

namespace App\Filament\Pages\Settings;

use Filament\Forms;
use Filament\Forms\Set;
use Livewire\Component;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Pages\SettingsPage;
use App\Settings\GeneralSettings;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Group;
use Filament\Support\Enums\MaxWidth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Actions\Action;
use Awcodes\Palette\Forms\Components\ColorPicker;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Awcodes\Palette\Forms\Components\ColorPickerSelect;

class ManageGeneral extends SettingsPage
{
  use HasPageShield;

  protected static ?string $navigationIcon = 'fas-cog';
  protected static ?string $title = 'General Settings';
  protected static ?string $slug = 'general-settings';
  protected ?string $subheading = "You can configure the website's appereance on this page.";
  protected static string $settings = GeneralSettings::class;

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::SETTING->getLabel();
  }

  public function getSavedNotificationTitle(): ?string
  {
    Notification::make()
      ->success()
      ->title('Settings saved')
      ->body('You need to refresh the page to apply some changes.')
      ->persistent()
      ->send();

    return null;
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        static::getSiteSettingsSection(),
        static::getColorPaletteSection(),
      ]);
  }

  public function getSiteSettingsSection(): Section
  {
    return Section::make('Site Settings')
      ->icon('mdi-web')
      ->columns(3)
      ->headerActions([
        Action::make('reset')
          ->link()
          ->requiresConfirmation()
          ->label('Reset to default')
          ->action(function (Set $set) {
            $set('site_name', env('APP_NAME', 'Yasuda Jaya Tour'));
            $set('site_font', 'Poppins');
            $set('site_logoHeight', 35);
            $set('site_maxContentWidth', MaxWidth::Full->value);
            $set('table_actionPosition', ActionsPosition::BeforeColumns->name);
            $set('site_navigation', 0);
            $set('table_striped', 0);
            $set('site_spa', 0);

            Notification::make()
              ->warning()
              ->body('Site settings have been reset to default')
              ->send();
          }),
      ])
      ->schema([
        TextInput::make('site_name')
          ->required()
          ->label('Name')
          ->placeholder(env('APP_NAME', 'Yasuda Jaya Tour'))
          ->helperText('This name will be displayed on the top of the site. Default: ' . env('APP_NAME', 'Yasuda Jaya Tour')),
        TextInput::make('site_font')
          ->required()
          ->label('Font')
          ->placeholder('Poppins')
          ->hint('Poppins, Open Sans, Roboto etc.')
          ->helperText(fn() => new HtmlString(<<<HTML
          You can see and search all the fonts name <a class="text-primary-500 font-semibold" href="https://fonts.google.com" target="_blank">here</a>. Default: Poppins
          HTML)),
        TextInput::make('site_logoHeight')
          ->integer()
          ->label('Logo Height')
          ->helperText('Set the logo height. Default: 35px')
          ->placeholder(35)
          ->suffix('px')
          ->required(),
        Group::make([
          Select::make('site_maxContentWidth')
            ->label('Max Content Width')
            ->placeholder(Str::headline(MaxWidth::Full->value))
            ->helperText('Choose the maximum content width. Default: Full')
            ->options(collect(MaxWidth::cases())
              ->mapWithKeys(static fn(MaxWidth $case) => [$case->value => Str::headline($case->name)]))
            ->required(),
          Select::make('table_actionPosition')
            ->label('Table Action Position')
            ->placeholder(Str::headline(ActionsPosition::BeforeColumns->name))
            ->helperText('Choose the table action position. Default: Before Columns')
            ->options(collect(ActionsPosition::cases())
              ->mapWithKeys(static fn(ActionsPosition $case) => [$case->name => Str::headline($case->name)]))
            ->required(),
        ])->columnSpanFull()->columns(2),
        ToggleButtons::make('site_navigation')
          ->required()
          ->inline()
          ->label('Navigation Layout')
          ->helperText('Choose between top or side navigation. Does not affect mobile devices. Default: Side Navigation.')
          ->boolean('Top Navigation', 'Side Navigation')
          ->icons([
            1 => 'fluentui-window-header-horizontal-20-o',
            0 => 'fluentui-window-header-vertical-20-o',
          ]),
        ToggleButtons::make('table_striped')
          ->required()
          ->inline()
          ->label('Table Layout')
          ->helperText('Choose between table striped or no strip. Default: No Strip.')
          ->boolean('Zebra Striped', 'No Strip')
          ->icons([
            1 => 'heroicon-s-table-cells',
            0 => 'heroicon-o-table-cells',
          ]),
        ToggleButtons::make('site_spa')
          ->required()
          ->grouped()
          ->helperText('Dynamically updates content within a single webpage, providing a seamless user experience without full page reloads. Default: Disabled.')
          ->label('Single Page Application')
          ->boolean('Enabled', 'Disabled'),
      ]);
  }

  public function getColorPaletteSection(): Section
  {
    $colors = Color::all();

    return Section::make('Color Palette')
      ->icon('gmdi-color-lens-r')
      ->columns(2)
      ->headerActions([
        Action::make('reset')
          ->link()
          ->requiresConfirmation()
          ->label('Reset to default')
          ->action(function (Set $set) {
            $set('color_primary', 'rose');
            $set('color_secondary', 'indigo');
            
            Notification::make()
              ->warning()
              ->body('Color palette have been reset to default')
              ->send();
          }),
      ])
      ->schema([
        Group::make([
          ColorPickerSelect::make('color_primary')
            ->required()
            ->label('Primary Color')
            ->colors($colors)
            ->searchable(false)
            ->placeholder('Rose'),
          ColorPicker::make('color_primary')
            ->required()
            ->live()
            ->hiddenLabel()
            ->colors($colors)
            ->helperText('Choose the primary color. Default: Rose'),
        ]),
        Group::make([
          ColorPickerSelect::make('color_secondary')
            ->required()
            ->label('Secondary Color')
            ->colors($colors)
            ->searchable(false)
            ->placeholder('Indigo'),
          ColorPicker::make('color_secondary')
            ->required()
            ->hiddenLabel()
            ->colors($colors)
            ->helperText('Choose the primary color. Default: Indigo'),
        ]),
      ]);
  }
}
