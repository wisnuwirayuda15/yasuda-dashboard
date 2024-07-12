<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Enums\Gender;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Employee;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use App\Filament\Resources\EmployeeResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\EmployeeResource\RelationManagers;

class EmployeeResource extends Resource
{
  protected static ?string $model = Employee::class;

  protected static ?string $navigationIcon = 'fluentui-people-team-toolbox-20';

  protected static ?int $navigationSort = -6;

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::HR->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->columns(3)
      ->schema([
        Section::make('General Information')
          ->columnSpan(2)
          ->schema([
            TextInput::make('code')
              ->live(true)
              ->code(fn(Get $get) => get_code(new Employee, $get('role') === EmployeeRole::TOUR_LEADER->value ? '02/TLF/' : '01/YSD/', false)),
            FileUpload::make('photo')
              ->image()
              ->imageEditor()
              ->maxSize(2048)
              ->directory('employee')
              ->imageCropAspectRatio('1:1')
              ->imageResizeMode('cover')
              ->columnSpanFull(),
            TextInput::make('name')
              ->required(),
            TextInput::make('alias')
              ->required()
              ->unique(ignoreRecord: true),
            DatePicker::make('join_date')
              ->required()
              ->label('Tanggal Masuk')
              ->default(today())
              ->maxDate(today())
              ->loadingIndicator(),
            TextInput::make('ktp')
              ->numeric()
              ->label('No. KTP')
              ->unique(ignoreRecord: true)
              ->maxLength(255),
            PhoneInput::make('phone')
              ->unique(ignoreRecord: true)
              ->indonesian(),
          ]),
        Grid::make()
          ->columnSpan(1)
          ->schema([
            Section::make('Other Information')
              ->schema([
                ToggleButtons::make('status')
                  ->required()
                  ->inline()
                  ->options(EmployeeStatus::class)
                  ->disableOptionWhen(fn (string $value, string $operation): bool => $operation === 'create' && ($value === EmployeeStatus::RESIGN->value || $value === EmployeeStatus::RETIRE->value))
                  ->loadingIndicator(),
                ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
                DatePicker::make('exit_date')
                  ->required()
                  ->label('Tanggal Keluar')
                  ->minDate(fn(Get $get) => $get('join_date'))
                  ->visible(fn(Get $get): bool => $get('status') === EmployeeStatus::RESIGN->value || $get('status') === EmployeeStatus::RETIRE->value)
                  ->loadingIndicator(),
                Select::make('role')
                  ->required()
                  ->label('Jabatan')
                  ->options(EmployeeRole::class)
                  ->afterStateUpdated(function (Set $set, ?string $state): void {
                    if (blank($state)) {
                      return;
                    }
                    $prefix = $state === EmployeeRole::TOUR_LEADER->value ? '02/TLF/' : '01/YSD/';
                    $code = get_code(new Employee, $prefix, false);
                    $set('code', $code);
                  })
                  ->loadingIndicator(),
              ])
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        ImageColumn::make('photo')
          ->circular(),
        IconColumn::make('is_user_assigned')
          ->label('Is User Assigned')
          ->state(fn(Employee $record) => $record->employable()->exists())
          ->tooltip(fn(Employee $record) => $record->employable?->name)
          ->boolean()
          ->alignCenter(),
        TextColumn::make('code')
          ->badge()
          ->searchable(),
        TextColumn::make('name')
          ->sortable()
          ->description(fn(Employee $record): string => $record->alias ?? null)
          ->searchable(),
        TextColumn::make('join_date')
          ->label('Tanggal Masuk')
          ->date()
          ->sortable(),
        TextColumn::make('ktp')
          ->label('No KTP')
          ->limit(7, '***')
          ->searchable()
          ->placeholder('No data'),
        TextColumn::make('gender')
          ->badge(),
        TextColumn::make('role')
          ->label('Jabatan')
          ->badge(),
        TextColumn::make('status')
          ->badge(),
        TextColumn::make('working_day')
          ->label('Masa Kerja (Hari)')
          ->numeric()
          ->state(function (Employee $record): string {
            return today()->diffInDays($record->join_date);
          })
          ->sortable(query: function (Builder $query, string $direction): Builder {
            return $query->orderBy('join_date', $direction);
          }),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        SelectFilter::make('role')
          ->multiple()
          ->options(EmployeeRole::class),
        SelectFilter::make('status')
          ->multiple()
          ->options(EmployeeStatus::class),
      ])
      ->actions([
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
          static::getAssignUserAction(),
          static::getCreateUserAction(),
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
      'index' => Pages\ListEmployees::route('/'),
      'create' => Pages\CreateEmployee::route('/create'),
      'view' => Pages\ViewEmployee::route('/{record}'),
      'edit' => Pages\EditEmployee::route('/{record}/edit'),
    ];
  }

  public static function getAssignUserAction(): Action
  {
    return Action::make('select_user')
      ->icon(UserResource::getNavigationIcon())
      ->label('Select User Account')
      ->color('info')
      // ->visible(fn(Employee $record) => $record->employable()->exists())
      ->form([
        Select::make('user_id')
          ->required()
          ->label('Select User')
          ->default(fn(Employee $record) => $record->employable?->id)
          ->options(function (Employee $record) {
            return User::whereNotMorphedTo('employable', Employee::class)
              ->orWhere('id', $record->employable?->id)
              ->pluck('name', 'id');
          })
      ])
      ->action(function (array $data, Employee $record): void {
        if ($record->employable()->exists()) {
          $record->employable()->update([
            'employable_id' => null,
            'employable_type' => null
          ]);
        }
        $user = User::find($data['user_id']);
        $record->employable()->save($user);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("User assigned for <strong>{$record->name}</strong>")
          ->send();
      });
  }

  public static function getCreateUserAction(): Action
  {
    return Action::make('create_user')
      ->icon('fas-user-plus')
      ->label('Create User Account')
      ->color('success')
      ->hidden(fn(Employee $record) => $record->employable()->exists())
      ->url(fn(Employee $record) => UserResource::getUrl('create', ['employee' => $record->id]));
  }
}
