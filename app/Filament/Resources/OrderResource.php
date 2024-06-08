<?php

namespace App\Filament\Resources;

use App\Enums\CustomerStatus;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\OrderResource\RelationManagers;
use Filament\Forms\Components\CheckboxList;

class OrderResource extends Resource
{
  protected static ?string $model = Order::class;

  protected static ?string $navigationIcon = 'heroicon-s-check-badge';

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\TextInput::make('code')
          ->code(get_code(new Order, 'OR')),
        Forms\Components\Select::make('customer_id')
          ->required()
          ->relationship('customer', 'name', fn(Builder $query) => $query->whereNot('status', CustomerStatus::CANDIDATE->value)->orderBy('created_at', 'desc'))
          ->editOptionModalHeading('Edit Customer')
          ->createOptionModalHeading('Create Customer')
          ->prefixIcon(fn() => CustomerResource::getNavigationIcon())
          ->editOptionForm(fn(Form $form) => CustomerResource::form($form))
          ->createOptionForm(fn(Form $form) => CustomerResource::form($form)),
        Forms\Components\Select::make('regency_id')
          ->required()
          ->relationship('regency', 'name'),
        Forms\Components\Select::make('destinations')
          ->required()
          ->multiple()
          ->options(Destination::pluck('name', 'id'))
          ->hintAction(Forms\Components\Actions\Action::make('select_tour_template')
            ->label('Template')
            ->icon('tabler-playlist-add')
            ->form([
              Forms\Components\Select::make('tour_template')
                ->required()
                ->options(TourTemplate::pluck('name', 'id')),
            ])
            ->action(function (array $data, Set $set) {
              $tourTemplate = TourTemplate::find($data)->toArray()[0];
              $set('regency_id', $tourTemplate['regency_id']);
              $set('destinations', $tourTemplate['destinations']);
            })),
        Toggle::make('change_date')
          ->live(true)
          ->hiddenOn(['create', 'createOption', 'createOption.createOption', 'editOption.createOption']),
        Forms\Components\DatePicker::make('trip_date')
          ->required()
          ->live(true)
          ->default(today())
          ->minDate(today())
          ->disabled(fn(Get $get, $operation) => in_array($operation, ['create', 'createOption', 'createOption.createOption', 'editOption.createOption']) ? false : !$get('change_date'))
          ->helperText(fn($operation) => in_array($operation, ['edit', 'editOption', 'editOption.editOption', 'createOption.editOption']) ? 'Jika diubah, semua jadwal armada yang sudah diatur untuk order ini akan dihapus.' : false)
          ->hint(fn($state) => today()->diffInDays($state) . ' hari sebelum keberangkatan')
          ->afterStateUpdated(fn(Set $set) => $set('order_fleet_ids', [])),
        Section::make('Jadwal Armada yang Tersedia')
          ->hiddenOn(['edit', 'editOption', 'editOption.editOption', 'createOption.editOption'])
          ->schema([
            CheckboxList::make('order_fleet_ids')
              ->hiddenLabel()
              ->columns(2)
              ->options(function (Get $get) {
                $orderFleets = OrderFleet::whereDate('trip_date', $get('trip_date'))
                  ->doesntHave('order')
                  ->with('fleet')
                  ->get()
                  ->mapWithKeys(function ($orderFleet) {
                    return [$orderFleet->id => "{$orderFleet->code} • {$orderFleet->fleet->name} • {$orderFleet->fleet->seat_set->getLabel()}"];
                  })
                  ->toArray();
                return $orderFleets;
              }),
          ]),
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
        Tables\Columns\TextColumn::make('trip_date')
          ->date()
          ->sortable()
          ->formatStateUsing(fn($state): string => $state->format('d/m/Y')),
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
      ])
      ->actions([
        ActionGroup::make([
          ViewAction::make(),
          EditAction::make(),
          DeleteAction::make()
            ->before(function (DeleteAction $action, Order $record) {
              $inv = $record->invoice()->exists();
              $of = $record->orderFleets()->exists();
              if ($inv || $of) {
                Notification::make()
                  ->danger()
                  ->title('Failed to delete!')
                  ->body($inv ? 'Invoice untuk order ini sudah dibuat.' : 'Order sudah dijadwalkan.')
                  ->persistent()
                  ->send();
                $action->cancel();
              }
            }),
        ])
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
