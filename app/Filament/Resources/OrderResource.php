<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
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
          ->default(get_code(new Order, 'OR'))
          ->helperText('Code is generated automatically.')
          ->unique(Order::class, 'code', ignoreRecord: true),
        Forms\Components\Select::make('customer_id')
          ->required()
          ->searchable()
          ->preload()
          ->optionsLimit(5)
          ->relationship('customer', 'name', fn(Builder $query) => $query->orderBy('created_at', 'desc'))
          ->editOptionModalHeading('Edit Customer')
          ->createOptionModalHeading('Create Customer')
          ->prefixIcon(fn() => CustomerResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => CustomerResource::form($form))
          ->createOptionForm(fn(Form $form) => CustomerResource::form($form)),
        Forms\Components\Select::make('regency_id')
          ->required()
          ->preload()
          ->searchable()
          ->relationship('regency', 'name'),
        Forms\Components\Select::make('destinations')
          ->required()
          ->multiple()
          ->searchable()
          ->options(Destination::pluck('name', 'id'))
          ->hintAction(Forms\Components\Actions\Action::make('select_tour_template')
            ->label('Select Tour Template')
            ->icon('tabler-playlist-add')
            ->form([
              Forms\Components\Select::make('tour_template')
                ->required()
                ->searchable()
                ->options(TourTemplate::query()->pluck('name', 'id')),
            ])
            ->action(function (array $data, Set $set) {
              $tourTemplate = TourTemplate::find($data)->toArray()[0];
              $set('regency_id', $tourTemplate['regency_id']);
              $set('destinations', $tourTemplate['destinations']);
            })),
        Forms\Components\DatePicker::make('trip_date')
          ->required()
          ->native(false)
          ->minDate(today())
          ->closeOnDateSelection()
          ->prefixIcon('heroicon-s-calendar-days')
          ->displayFormat('d mm Y'),
        Forms\Components\RichEditor::make('description')
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
