<?php

namespace App\Filament\Resources;

use Filament\Forms;
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
use App\Enums\NavigationGroupLabel;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\FleetResource\Pages;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\FleetResource\RelationManagers;

class FleetResource extends Resource
{
  protected static ?string $model = Fleet::class;

  protected static ?string $navigationIcon = 'fas-bus';

  protected static ?string $navigationGroup = NavigationGroupLabel::MASTER_DATA->value;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\FileUpload::make('image')
          ->required()
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('fleet')
          ->imageCropAspectRatio('16:9')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        Forms\Components\TextInput::make('name')
          ->required()
          ->maxLength(255),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        Forms\Components\ToggleButtons::make('category')
          ->required()
          ->live()
          ->inline()
          ->options(FleetCategory::class)
          ->default(FleetCategory::MEDIUM->value)
          ->afterStateUpdated(fn(Set $set) => $set('seat_set', null)),
        Forms\Components\ToggleButtons::make('seat_set')
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
        Forms\Components\TextInput::make('pic_name')
          ->required()
          ->maxLength(255),
        PhoneInput::make('pic_phone')
          ->focusNumberFormat(PhoneInputNumberType::E164)
          ->defaultCountry('ID')
          ->initialCountry('id')
          ->showSelectedDialCode(true)
          ->formatAsYouType(false)
          ->required()
          ->rules('phone:mobile'),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('image'),
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('seat_set')
          ->sortable(),
        Tables\Columns\TextColumn::make('category')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('pic_name')
          ->searchable(),
        PhoneColumn::make('pic_phone')
          ->displayFormat(PhoneInputNumberType::NATIONAL)
          ->searchable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('deleted_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
