<?php

namespace App\Filament\Resources;

use Closure;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use App\Models\Fleet;
use App\Models\Order;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Enums\FleetSeat;
use App\Models\Employee;
use Filament\Forms\Form;
use App\Models\OrderFleet;
use App\Models\TourLeader;
use Filament\Tables\Table;
use App\Enums\EmployeeRole;
use App\Enums\FleetCategory;
use App\Enums\OrderFleetStatus;
use Filament\Resources\Resource;
use App\Enums\FleetPaymentStatus;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use App\Filament\Resources\OrderResource;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Exports\OrderFleetExporter;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\OrderFleetResource\Pages;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use App\Filament\Resources\OrderFleetResource\RelationManagers;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class OrderFleetResource extends Resource
{
  protected static ?string $model = OrderFleet::class;

  protected static ?string $navigationIcon = 'mdi-bus-marker';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::OPERATIONAL->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form->schema([
      TextInput::make('code')
        ->code(get_code(new OrderFleet, 'OF')),
      Select::make('fleet_id')
        ->required()
        ->label('Armada')
        ->options(Fleet::getGroupOptionsByCategories()),
      DatePicker::make('trip_date')
        ->required()
        ->live(true)
        ->default(today())
        ->minDate(today())
        ->disabled(fn(?OrderFleet $record) => $record?->order()->exists())
        ->helperText(fn(?OrderFleet $record) => $record?->order()->exists() ? 'Order already added' : null)
        ->columnSpan(fn(string $operation) => in_array($operation, ['create', 'view']) ? 'full' : null),
      Section::make('Pembayaran Armada')
        ->schema([
          ToggleButtons::make('payment_status')
            ->required()
            ->label('Status')
            ->inline()
            ->options(FleetPaymentStatus::class)
            ->default(FleetPaymentStatus::NON_DP->value)
            ->afterStateUpdated(function (Get $get, Set $set) {
              if ($get('payment_status') === FleetPaymentStatus::NON_DP->value) {
                $set('payment_date', null);
                $set('payment_amount', null);
              }
            })
            ->loadingIndicator(),
          Group::make()
            ->hidden(fn(Get $get) => $get('payment_status') === FleetPaymentStatus::NON_DP->value)
            ->columnSpan(2)
            ->schema([
              DatePicker::make('payment_date')
                ->required()
                ->label('Tgl. Bayar'),
              TextInput::make('payment_amount')
                ->required()
                ->label('Jumlah Bayar')
                ->currency(minValue: 1)
            ])
        ]),
    ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->defaultSort('remaining_day')
      ->persistSortInSession()
      ->columns([
        TextColumn::make('code')
          ->badge()
          ->sortable()
          ->searchable(),
        TextColumn::make('order.customer.name')
          ->sortable()
          ->placeholder('No customer')
        // ->tooltip(fn(OrderFleet $record) => ($record->order_id ? 'Change' : 'Select') . ' order')
        // ->action(static::getSelectOrderAction())
        ,
        TextColumn::make('employee.name')
          ->sortable()
          ->placeholder('No tour leader')
        // ->tooltip(fn(OrderFleet $record) => ($record->employee_id ? 'Change' : 'Select') . ' tour leader')
        // ->action(static::getSelectTourLeaderAction())
        ,
        TextColumn::make('trip_date')
          ->date()
          ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y')),
        TextColumn::make('remaining_day')
          ->badge()
          ->alignCenter()
          ->sortable(query: function (Builder $query, string $direction): Builder {
            return $query->orderBy('trip_date', $direction);
          })
          ->state(
            function (OrderFleet $record): string {
              $date = $record->trip_date;
              return match (true) {
                $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
                $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
                default => today()->diffInDays($date),
              };
            }
          )
          ->color(fn(string $state) => match ($state) {
            OrderFleetStatus::ON_TRIP->getLabel() => OrderFleetStatus::ON_TRIP->getColor(),
            default => match (true) {
                $state <= 7 => 'danger',
                $state <= 30 => 'warning',
                default => 'success',
              },
          }),
        TextColumn::make('status')
          ->badge()
          ->color(fn(string $state) => match ($state) {
            OrderFleetStatus::BOOKED->getLabel() => OrderFleetStatus::BOOKED->getColor(),
            OrderFleetStatus::ON_TRIP->getLabel() => OrderFleetStatus::ON_TRIP->getColor(),
            OrderFleetStatus::FINISHED->getLabel() => OrderFleetStatus::FINISHED->getColor(),
            default => OrderFleetStatus::READY->getColor(),
          })
          ->state(
            function (OrderFleet $record): string {
              $date = $record->trip_date;
              $order = $record->order()->exists();
              return match (true) {
                $order => OrderFleetStatus::BOOKED->getLabel(),
                $date->isToday() => OrderFleetStatus::ON_TRIP->getLabel(),
                $date->isPast() => OrderFleetStatus::FINISHED->getLabel(),
                default => OrderFleetStatus::READY->getLabel(),
              };
            }
          ),
        TextColumn::make('fleet.name')
          ->label('Mitra Armada'),
        TextColumn::make('fleet.category')
          ->label('Jenis')
          ->badge(),
        TextColumn::make('fleet.seat_set')
          ->label('Seat Set'),
        TextColumn::make('trip_day')
          ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('l')),
        TextColumn::make('trip_month')
          ->state(fn(OrderFleet $record): string => $record->trip_date->translatedFormat('F')),
        TextColumn::make('payment_status')
          ->badge()
          ->searchable(),
        TextColumn::make('payment_date')
          ->date()
          ->formatStateUsing(fn(Carbon $state): string => $state->translatedFormat('d/m/Y'))
          ->placeholder('Unpaid')
          ->sortable(),
        TextColumn::make('payment_amount')
          ->money('IDR')
          ->placeholder(idr(0))
          ->sortable(),
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
        DateRangeFilter::make('trip_date')
          ->label('Trip Date'),
        Filter::make('has_order')
          ->label('Sudah terdapat order')
          ->query(fn(Builder $query): Builder => $query->whereHas('order')),
        Filter::make('has_tour_leader')
          ->label('Sudah terdapat tour leader')
          ->query(fn(Builder $query): Builder => $query->whereHas('employee')),
        Filter::make('approved')->approval(),
      ])
      ->headerActions([
        ExportAction::make()
          ->hidden(fn(): bool => static::getModel()::count() === 0)
          ->exporter(OrderFleetExporter::class)
          ->label('Export')
          ->color('success')
      ])
      ->actions([
        ActionGroup::make([
          SubmitAction::make(),
          ApproveAction::make(),
          DiscardAction::make(),
          RejectAction::make(),
        ])->label(__('filament-approvals::approvals.actions.approvals'))
          ->icon('heroicon-m-ellipsis-vertical')
          ->size(ActionSize::Small)
          ->color('primary')
          ->button(),
        ActionGroup::make([
          ViewAction::make(),
          EditAction::make(),
          DeleteAction::make(),
          ReplicateAction::make()
            ->color('warning')
            ->modal(false)
            ->excludeAttributes(['order_id', 'employee_id'])
            ->before(function (OrderFleet $record) {
              $record->code = get_code(new OrderFleet, 'OF');
            }),
          ActionGroup::make([
            static::getSelectOrderAction(),
            static::getSelectTourLeaderAction(),
          ])->dropdown(false),
          ActionGroup::make([
            static::getDeleteOrderAction(),
            static::getDeleteTourLeaderAction(),
          ])->dropdown(false),
        ])
          ->visible(function (?OrderFleet $record): ?bool {
            $approved = $record?->isApprovalCompleted();
            if (blank($approved)) {
              return true;
            }
            return (bool) $approved;
          })
      ])
      ->bulkActions([
        BulkActionGroup::make([
          static::getSelectOrderBulkAction(),
          static::getDeleteOrderBulkAction(),
          static::getDeleteTourLeaderBulkAction(),
        ]),
      ])
    ;
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
      'index' => Pages\ListOrderFleets::route('/'),
      'create' => Pages\CreateOrderFleet::route('/create'),
      'view' => Pages\ViewOrderFleet::route('/{record}'),
      'edit' => Pages\EditOrderFleet::route('/{record}/edit'),
    ];
  }

  public static function getSelectOrderAction(): Action
  {
    return Action::make('select_order_id')
      ->icon(OrderResource::getNavigationIcon())
      ->label('Select Order')
      ->color('info')
      ->form([
        Select::make('order_id')
          ->required()
          ->hiddenLabel()
          ->allowHtml()
          ->default(fn(OrderFleet $record) => $record->order_id)
          ->prefixIcon(OrderResource::getNavigationIcon())
          ->relationship('order', modifyQueryUsing: fn(Builder $query, OrderFleet $record): Builder => $query->whereDate('trip_date', $record->trip_date)->with('customer'))
          ->getOptionLabelFromRecordUsing(fn(Order $record) => view('filament.components.badges.order', compact('record')))
          ->rules([
            fn(OrderFleet $record): Closure => function (string $attribute, $value, Closure $fail) use ($record) {
              $order = Order::findOrFail($value);
              if (!$order->trip_date->isSameDay($record->trip_date)) {
                $fail('Order trip date must be the same as the order fleet trip date');
              }
            },
          ]),
      ])
      ->action(function (array $data, OrderFleet $record): void {
        $record->update(['order_id' => $data['order_id']]);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("Order added for {$record->code}")
          ->send();
      });
  }

  public static function getSelectOrderBulkAction(): BulkAction
  {
    return BulkAction::make('bulk_select_order_id')
      ->icon(OrderResource::getNavigationIcon())
      ->label('Select Orders')
      ->color('info')
      ->beforeFormFilled(function (Collection $records, BulkAction $action) {
        $dates = $records->pluck('trip_date')->map(function (Carbon $date) {
          return $date->toDateString();
        });

        // check if all record have same trip date. if not, cancel the process
        if ($dates->unique()->count() > 1) {
          Notification::make()
            ->danger()
            ->title('Failed')
            ->body('Ketersediaan armada yang dipilih harus memiliki tanggal yang sama')
            ->send();

          $action->cancel();
        }
      })
      ->form(fn(Collection $records) => [
        Select::make('order_id')
          ->required()
          ->hiddenLabel()
          ->allowHtml()
          ->prefixIcon(OrderResource::getNavigationIcon())
          ->options(
            function () use ($records) {
              return Order::whereDate('trip_date', $records->first()->trip_date->toDateString())->with('customer')->get()->mapWithKeys(function (Order $order) {
                return [$order->id => view('filament.components.badges.order', ['record' => $order])->render()];
              })->toArray();
            }
          )
      ])
      ->action(function (array $data, Collection $records): void {
        $codes = $records->pluck('code')->implode(', ');

        $records->each->update(['order_id' => $data['order_id']]);

        Notification::make()
          ->success()
          ->title('Success')
          ->body("Order added for <strong>{$codes}</strong>")
          ->send();
      });
  }

  public static function getDeleteOrderAction(): Action
  {
    return Action::make('delete_order_id')
      ->requiresConfirmation()
      ->icon('heroicon-s-trash')
      ->label('Remove Order')
      ->color('danger')
      ->hidden(fn(OrderFleet $record): bool => blank($record->order_id))
      ->action(
        function (OrderFleet $record) {
          $record->update(['order_id' => null]);
          Notification::make()
            ->success()
            ->title('Success')
            ->body("Order removed from {$record->code}")
            ->send();
        }
      );
  }

  public static function getDeleteOrderBulkAction(): BulkAction
  {
    return BulkAction::make('bulk_delete_order_id')
      ->requiresConfirmation()
      ->icon('heroicon-s-trash')
      ->label('Remove Orders')
      ->color('danger')
      ->action(
        function (Collection $records) {
          $codes = $records->pluck('code')->implode(', ');

          $records->each->update(['order_id' => null]);

          Notification::make()
            ->success()
            ->title('Success')
            ->body("Order removed from <strong>{$codes}</strong>")
            ->send();
        }
      );
  }

  public static function getSelectTourLeaderAction(): Action
  {
    return Action::make('select_employee_id')
      ->icon('gmdi-tour')
      ->label('Select Tour Leader')
      ->color('success')
      ->form([
        Select::make('employee_id')
          ->required()
          ->hiddenLabel()
          ->default(fn(OrderFleet $record) => $record->employee_id)
          ->prefixIcon('gmdi-tour')
          ->relationship(
            'employee',
            'name',
            function (Builder $query, OrderFleet $record): Builder {
              return $query->where('role', EmployeeRole::TOUR_LEADER->value)->whereDoesntHave('orderFleets', function (Builder $query) use ($record) {
                $query->whereDate('trip_date', $record->trip_date);
              })->orWhere('id', $record->employee_id);
            }
          )
      ])
      ->action(function (array $data, OrderFleet $record): void {
        $record->update(['employee_id' => $data['employee_id']]);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("Tour leader added for {$record->code}")
          ->send();
      });
  }

  public static function getDeleteTourLeaderAction(): Action
  {
    return Action::make('delete_employee_id')
      ->requiresConfirmation()
      ->icon('heroicon-s-trash')
      ->label('Remove Tour Leader')
      ->color('danger')
      ->hidden(fn(OrderFleet $record): bool => blank($record->employee_id))
      ->action(
        function (OrderFleet $record) {
          $record->update(['employee_id' => null]);
          Notification::make()
            ->success()
            ->title('Success')
            ->body("Tour leader removed from {$record->code}")
            ->send();
        }
      );
  }

  public static function getDeleteTourLeaderBulkAction(): BulkAction
  {
    return BulkAction::make('bulk_delete_employee_id')
      ->requiresConfirmation()
      ->icon('heroicon-s-trash')
      ->label('Remove Tour Leaders')
      ->color('danger')
      ->action(
        function (Collection $records) {
          $codes = $records->pluck('code')->implode(', ');

          $records->each->update(['employee_id' => null]);

          Notification::make()
            ->success()
            ->title('Success')
            ->body("Tour leader removed from <strong>{$codes}</strong>")
            ->send();
        }
      );
  }
}
