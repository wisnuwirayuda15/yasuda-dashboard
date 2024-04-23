<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;

class OrderResource extends Resource
{
  protected static ?string $model = Order::class;

  protected static ?string $navigationIcon = 'heroicon-s-check-badge';

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->required()
          ->disabled()
          ->dehydrated()
          ->default(get_code(new Order, 'OR-'))
          ->helperText('Code is generated automatically.')
          ->unique(Order::class, 'code', ignoreRecord: true),
        Forms\Components\Select::make('customer_id')
          ->required()
          ->preload()
          ->searchable()
          ->native(false)
          ->prefixIcon(fn() => CustomerResource::getNavigationIcon())
          ->relationship('customer', 'name')
          ->editOptionForm(fn(Form $form) => CustomerResource::form($form))
          ->createOptionForm(fn(Form $form) => CustomerResource::form($form))
          ->editOptionModalHeading('Edit Customer')
          ->createOptionModalHeading('Create Customer'),
        Forms\Components\Select::make('regency_id')
          ->required()
          ->searchable()
          ->native(false)
          ->preload()
          ->relationship('regency', 'name'),
        Forms\Components\Select::make('destinations')
          ->required()
          ->multiple()
          ->searchable()
          ->hintAction(Forms\Components\Actions\Action::make('select_tour_template')
            ->icon('tabler-playlist-add')
            ->form([
              Forms\Components\Select::make('tour_template')
                ->required()
                ->searchable()
                ->live()
                ->options(TourTemplate::query()->pluck('name', 'id')),
            ])
            ->action(function (array $data, Set $set) {
              $tourTemplate = TourTemplate::findOrFail($data)->toArray()[0];
              $set('regency_id', $tourTemplate['regency_id']);
              $set('destinations', $tourTemplate['destinations']);
            }), )
          ->native(false)
          ->options(Destination::pluck('name', 'id')),
        Forms\Components\RichEditor::make('description')
          ->required()
          ->columnSpanFull(),
      ]);
  }

  public static function table(Table $table): Table
  {
    $destination = Destination::all();

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
          ->badge()
          ->formatStateUsing(fn(string $state): string => $destination->find($state)->name),
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
