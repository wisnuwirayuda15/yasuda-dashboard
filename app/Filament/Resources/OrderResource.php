<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
  protected static ?string $model = Order::class;

  protected static ?string $navigationGroup = 'Operational';

  protected static ?string $modelLabel = 'Order';

  // protected static ?string $navigationLabel = 'Order';

  protected static ?string $navigationIcon = 'heroicon-o-check-badge';

  protected static ?string $activeNavigationIcon = 'heroicon-s-check-badge';

  protected static ?int $navigationSort = 2;

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Select::make('customer_id')
          ->required()
          ->prefixIcon('heroicon-s-user-group')
          ->relationship('customer', 'name')
          ->native(false)
          ->searchable()
          ->preload()
          ->createOptionForm(schema: fn(Form $form) => CustomerResource::form($form)),
        Forms\Components\Select::make('tour_package_id')
          ->required()
          ->relationship('tourPackage', 'name')
          ->native(false)
          ->preload()
          ->searchable(),
        Forms\Components\TextInput::make('number_of_people')
          ->required()
          ->numeric(),
        Forms\Components\TextInput::make('payment_status')
          ->required()
          ->maxLength(50),
        Forms\Components\TextInput::make('banner_status')
          ->required()
          ->maxLength(50),
        Forms\Components\TextInput::make('status')
          ->required()
          ->maxLength(50),
        Flatpickr::make('start_date')
          ->required()
          ->range()
          ->live()
          ->dateFormat('d M Y')
          ->theme(FlatpickrTheme::DARK),
        Forms\Components\DatePicker::make('end_date')
          ->required()
          ->native(false)
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('customer.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('tourPackage.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('number_of_people')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('payment_status')
          ->searchable(),
        Tables\Columns\TextColumn::make('banner_status')
          ->searchable(),
        Tables\Columns\TextColumn::make('status')
          ->searchable(),
        Tables\Columns\TextColumn::make('start_date')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('end_date')
          ->dateTime()
          ->sortable(),
        Tables\Columns\TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        Tables\Columns\TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
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
      'index' => Pages\ListOrders::route('/'),
      'create' => Pages\CreateOrder::route('/create'),
      'view' => Pages\ViewOrder::route('/{record}'),
      'edit' => Pages\EditOrder::route('/{record}/edit'),
    ];
  }
}
