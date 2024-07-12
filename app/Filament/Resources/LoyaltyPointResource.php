<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Enums\CashFlow;
use App\Models\Invoice;
use Filament\Forms\Form;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use App\Enums\FleetCategory;
use App\Models\LoyaltyPoint;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LoyaltyPointResource\Pages;
use App\Filament\Resources\LoyaltyPointResource\RelationManagers;

class LoyaltyPointResource extends Resource
{
  protected static ?string $model = LoyaltyPoint::class;

  protected static ?string $navigationIcon = 'heroicon-s-star';

  protected static ?int $navigationSort = -7;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MARKETING->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        ToggleButtons::make('cash_status')
          ->required()
          ->inline()
          ->disabled()
          ->dehydrated()
          ->options(CashFlow::class)
          ->default(CashFlow::IN->value),
        TextInput::make('amount')
          ->required()
          ->currency(minValue: 1),
        RichEditor::make('description')
          ->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('invoice.order.customer.code')
          ->badge()
          ->searchable(),
        TextColumn::make('invoice.order.customer.name')
          ->searchable(),
        // TextColumn::make('description')
        //   ->html(),
        // TextColumn::make('cash_status')
        //   ->badge(),
        TextColumn::make('invoice.code')
          ->badge()
          ->sortable(),
        TextColumn::make('invoice.order.trip_date')
          ->label('Tanggal Pelaksanaan')
          ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
        TextColumn::make('medium_bus_total')
          ->label('Medium Bus')
          ->numeric()
          ->badge()
          ->color(FleetCategory::MEDIUM->getColor())
          ->state(function (LoyaltyPoint $record) {
            return $record->invoice->order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::MEDIUM->value)->count();
          }),
        TextColumn::make('big_bus_total')
          ->label('Big Bus')
          ->numeric()
          ->badge()
          ->color(FleetCategory::BIG->getColor())
          ->state(function (LoyaltyPoint $record) {
            return $record->invoice->order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::BIG->value)->count();
          }),
        TextColumn::make('legrest_bus_total')
          ->label('Legrest Bus')
          ->numeric()
          ->badge()
          ->color(FleetCategory::LEGREST->getColor())
          ->state(function (LoyaltyPoint $record) {
            return $record->invoice->order->orderFleets->filter(fn(OrderFleet $orderFleet) => $orderFleet->fleet->category->value === FleetCategory::LEGREST->value)->count();
          }),
        TextColumn::make('amount')
          ->label('Total Value')
          ->money('IDR')
          ->sortable(),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\DeleteAction::make(),
        ])
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
      'index' => Pages\ListLoyaltyPoints::route('/'),
      // 'create' => Pages\CreateLoyaltyPoint::route('/create'),
      // 'view' => Pages\ViewLoyaltyPoint::route('/{record}'),
      // 'edit' => Pages\EditLoyaltyPoint::route('/{record}/edit'),
    ];
  }
}
