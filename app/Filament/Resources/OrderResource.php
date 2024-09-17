<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\OrderFleet;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use App\Enums\CustomerStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use App\Filament\Exports\OrderExporter;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use App\Filament\Resources\OrderResource\RelationManagers;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;

class OrderResource extends Resource
{
  protected static ?string $model = Order::class;

  protected static ?string $navigationIcon = 'heroicon-s-check-badge';

  protected static ?int $navigationSort = 0;

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
        TextInput::make('code')
          ->code(get_code(new Order, 'OR')),
        Select::make('customer_id')
          ->required()
          ->columnSpan(fn(string $operation) => $operation === 'edit' ? 'full' : null)
          ->relationship('customer', 'name', fn(Builder $query) => $query->whereNot('status', CustomerStatus::CANDIDATE->value)->orderBy('created_at', 'desc'))
          ->prefixIcon(fn() => CustomerResource::getNavigationIcon()),
        Select::make('destinations')
          ->required()
          ->multiple()
          ->columnSpanFull()
          ->options(Destination::getOptions(false))
          ->hintAction(Action::make('select_tour_template')
            ->label(TourTemplateResource::getModelLabel())
            ->icon(TourTemplateResource::getNavigationIcon())
            ->hidden(fn(string $operation) => $operation === 'view')
            ->form([
              Select::make('tour_template')
                ->required()
                ->hiddenLabel()
                ->options(TourTemplate::pluck('name', 'id'))
            ])
            ->action(function (array $data, Set $set, Select $component) {
              $tourTemplate = TourTemplate::find($data)->toArray()[0];
              $regency = Regency::find($tourTemplate['regency_id']);
              $set('province_id', $regency->province_id);
              $set('regency_id', $tourTemplate['regency_id']);
              $set($component, $tourTemplate['destinations']);
            })),
        RegionSelects(false),
        Group::make([
          Toggle::make('change_date')
            ->live()
            ->label('Ubah Tanggal Keberangkatan')
            ->visible(fn(Order $record) => $record->trip_date)
            ->hiddenOn(['create', 'view'])
            ->afterStateUpdated(fn(Order $record, Set $set) => $set('trip_date', $record->trip_date))
            ->helperText(fn(string $operation) => $operation === 'edit' ? 'Jika diubah, semua jadwal armada yang sudah diatur untuk order ini akan dihapus.' : null)
            ->loadingIndicator(),
          DatePicker::make('trip_date')
            ->required()
            ->default(today())
            // ->minDate(today())
            ->disabled(fn(Get $get, string $operation) => $operation === 'create' ? false : !$get('change_date'))
            ->columnSpan(fn(string $operation) => in_array($operation, ['create', 'view']) ? 'full' : null)
            ->helperText(function (?string $state) {
              $diff = today()->diffInDays($state);
              return $diff === 0
                ? 'Berangkat hari ini'
                : "{$diff} hari sebelum keberangkatan";
            })
            ->afterStateUpdated(fn(Set $set) => $set('order_fleet_ids', []))
            ->loadingIndicator(),
        ])->columns(2)
          ->columnSpanFull(),
        Section::make('Jadwal Armada yang Tersedia')
          ->hiddenOn(['edit', 'view'])
          ->schema([
            CheckboxList::make('order_fleet_ids')
              ->hiddenLabel()
              ->columns(2)
              ->searchable()
              ->options(function (Get $get) {
                return OrderFleet::whereDate('trip_date', $get('trip_date'))
                  ->doesntHave('order')
                  ->with('fleet')
                  ->get()
                  ->mapWithKeys(function (OrderFleet $orderFleet) {
                    return [$orderFleet->id => "{$orderFleet->code} • {$orderFleet->fleet->name} • {$orderFleet->fleet->seat_set->getLabel()}"];
                  })
                  ->toArray();
              }),
          ]),
        RichEditor::make('description')->columnSpanFull(),
        Checkbox::make('submission')->submission()
      ]);
  }

  public static function table(Table $table): Table
  {
    $destination = Destination::all();

    return $table
      ->columns([
        TextColumn::make('code')
          ->badge()
          ->sortable()
          ->searchable(),
        TextColumn::make('customer.name')
          ->numeric()
          ->searchable()
          ->sortable(),
        TextColumn::make('trip_date')
          ->date()
          ->sortable()
          ->date('d/m/Y'),
        TextColumn::make('regency.name')
          ->label('Kota')
          ->searchable(),
        TextColumn::make('destinations')
          ->badge()
          ->formatStateUsing(fn(?string $state): ?string => $destination->find($state)?->name),
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
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          ->exporter(OrderExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->filters([
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
        ])->visible(fn(Model $record) => $record->isApprovalCompleted())
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
