<?php

namespace App\Filament\Resources;

use Filament\Tables;
use App\Models\Fleet;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\BigFleetSeat;
use App\Enums\FleetCategory;
use App\Enums\MediumFleetSeat;
use App\Enums\LegrestFleetSeat;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\FleetResource\Pages;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\FleetResource\RelationManagers;

class FleetResource extends Resource
{
  protected static ?string $model = Fleet::class;

  protected static ?string $navigationIcon = 'fas-bus';

  protected static ?string $recordTitleAttribute = 'name';

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function getGlobalSearchResultDetails(Model $record): array
  {
    return [
      $record->category->getLabel() . ' • ' . $record->seat_set->getLabel(),
    ];
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        FileUpload::make('image')
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('fleet')
          ->imageCropAspectRatio('16:9')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        TextInput::make('name')
          ->required()
          ->maxLength(255),
        RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        ToggleButtons::make('category')
          ->required()
          ->live()
          ->inline()
          ->options(FleetCategory::class)
          ->default(FleetCategory::MEDIUM->value)
          ->afterStateUpdated(fn(Set $set) => $set('seat_set', null)),
        ToggleButtons::make('seat_set')
          ->required()
          ->live()
          ->inline()
          ->helperText(fn(Get $get): string => "Select seat set for {$get('category')} fleet.")
          ->hidden(fn(Get $get): bool => !$get('category'))
          ->options(fn(Get $get) => match ($get('category')) {
            FleetCategory::MEDIUM->value => MediumFleetSeat::class,
            FleetCategory::BIG->value => BigFleetSeat::class,
            FleetCategory::LEGREST->value => LegrestFleetSeat::class,
            default => [],
          }),
        TextInput::make('pic_name')
          ->required()
          ->maxLength(255),
        PhoneInput::make('pic_phone')
          ->required()
          ->idDefaultFormat(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        ImageColumn::make('image'),
        TextColumn::make('name')
          ->searchable(),
        TextColumn::make('seat_set')
          ->sortable(),
        TextColumn::make('category')
          ->badge()
          ->searchable(),
        TextColumn::make('pic_name')
          ->searchable(),
        PhoneColumn::make('pic_phone')
          ->displayFormat(PhoneInputNumberType::NATIONAL)
          ->searchable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
          Tables\Actions\ReplicateAction::make()
            ->color('warning')
            ->modal(false)
        ]),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListFleets::route('/'),
      'create' => Pages\CreateFleet::route('/create'),
      'view' => Pages\ViewFleet::route('/{record}'),
      'edit' => Pages\EditFleet::route('/{record}/edit'),
    ];
  }
}
