<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Enums\DestinationType;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Filters\SelectFilter;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use App\Filament\Resources\DestinationResource\Pages;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use App\Filament\Resources\DestinationResource\RelationManagers;

class DestinationResource extends Resource
{
  protected static ?string $model = Destination::class;

  protected static ?string $navigationIcon = 'fas-map-location-dot';

  protected static ?string $recordTitleAttribute = 'name';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->unique(ignoreRecord: true)
          ->maxLength(255),
        Select::make('type')
          ->required()
          ->default(DestinationType::SISWA_ONLY->value)
          ->options(DestinationType::class),
        TextInput::make('marketing_name')
          ->required()
          ->maxLength(255),
        PhoneInput::make('marketing_phone')
          ->required()
          ->indonesian(),
        TextInput::make('weekday_price')
          ->required()
          ->default(0)
          ->currency(minValue: 0),
        TextInput::make('weekend_price')
          ->required()
          ->default(0)
          ->currency(minValue: 0),
        TextInput::make('high_season_price')
          ->default(0)
          ->currency(minValue: 0),
        Checkbox::make('submission')
          ->columnSpanFull()
          ->submission()
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name')
          ->searchable(),
        TextColumn::make('type')
          ->badge()
          ->searchable(),
        TextColumn::make('marketing_name')
          ->searchable(),
        TextColumn::make('marketing_phone')
          ->searchable(),
        TextColumn::make('weekday_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        TextColumn::make('weekend_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        TextColumn::make('high_season_price')
          ->numeric()
          ->sortable()
          ->money('IDR'),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        ApprovalStatusColumn::make('approvalStatus.status'),
      ])
      ->filters([
        SelectFilter::make('type')
          ->multiple()
          ->options(DestinationType::class),
        Filter::make('approved')->approved(),
        Filter::make('notApproved')->notApproved(),
      ])
      ->actions([
        SubmitAction::make()->color('info'),
        ApproveAction::make()->color('success'),
        DiscardAction::make()->color('warning'),
        RejectAction::make()->color('danger'),
        ActionGroup::make([
          ViewAction::make(),
          EditAction::make(),
          DeleteAction::make(),
        ])->visible(fn(Model $record) => $record->isApprovalCompleted())
      ])
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
      'index' => Pages\ListDestinations::route('/'),
      'create' => Pages\CreateDestination::route('/create'),
      'view' => Pages\ViewDestination::route('/{record}'),
      'edit' => Pages\EditDestination::route('/{record}/edit'),
    ];
  }
}
