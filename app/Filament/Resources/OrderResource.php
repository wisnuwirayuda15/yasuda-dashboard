<?php

namespace App\Filament\Resources;

use App\Models\Destination;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Regency;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
  protected static ?string $model = Order::class;

  protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->default(get_code(new Order, 'OR-'))
          ->helperText('Code are generated automatically.')
          ->unique(Order::class, 'code', ignoreRecord: true),
        Forms\Components\Select::make('customer_id')
          ->required()
          ->preload()
          ->searchable()
          ->native(false)
          ->prefixIcon('heroicon-s-user-group')
          ->relationship('customer', 'name')
          // ->editOptionForm(fn(Form $form) => CustomerResource::form($form))
          ->createOptionForm(fn(Form $form) => CustomerResource::form($form))
          // ->editOptionModalHeading('Edit Customer')
          ->createOptionModalHeading('Create Customer'),
        Forms\Components\Select::make('regency_id')
          ->required()
          ->searchable()
          ->native(false)
          ->options(Regency::orderBy('name')->pluck('name', 'id')),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
        Forms\Components\Select::make('destinations')
          ->required()
          ->multiple()
          ->searchable()
          ->native(false)
          ->options(Destination::pluck('name', 'id')),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('code')
          ->searchable(),
        Tables\Columns\TextColumn::make('customer.name')
          ->numeric()
          ->sortable(),
        Tables\Columns\TextColumn::make('regency.name')
          ->label('Kota')
          ->searchable(),
        Tables\Columns\TextColumn::make('destinations')
          ->searchable()
          ->formatStateUsing(fn(string $state): string => count(explode(", ", $state)) . " destinasi"),
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
