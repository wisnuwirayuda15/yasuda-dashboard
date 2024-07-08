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
use Filament\Tables\Filters\Filter;
use App\Filament\Exports\FleetExporter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use App\Filament\Resources\FleetResource\Pages;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use App\Filament\Resources\FleetResource\RelationManagers;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;

class FleetResource extends Resource
{
  protected static ?string $model = Fleet::class;

  protected static ?string $navigationIcon = 'fas-bus';

  protected static ?string $recordTitleAttribute = 'name';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
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
          ->maxLength(255)
          ->columnSpanFull(),
        RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        ToggleButtons::make('category')
          ->required()
          ->inline()
          ->options(FleetCategory::class)
          ->default(FleetCategory::MEDIUM->value)
          ->afterStateUpdated(fn(Set $set) => $set('seat_set', null))
          ->loadingIndicator(),
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
          ->indonesian(),
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
          ->label('PIC')
          ->searchable(),
        PhoneColumn::make('pic_phone')
          ->label('Phone')
          ->searchable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        ApprovalStatusColumn::make('approvalStatus.status')
          ->label('Approval Status')
          ->sortable(),
      ])
      ->filters([
        Filter::make('approved')->approval(),
      ])
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          ->exporter(FleetExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->actions(
        ApprovalActions::make([
          ActionGroup::make([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
          ]),
        ])
      )
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
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
