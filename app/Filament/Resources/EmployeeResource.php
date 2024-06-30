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
use App\Models\TourLeader;
use Filament\Tables\Table;
use App\Enums\EmployeeRole;
use App\Enums\EmployeeStatus;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
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

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::HR->getLabel();
  }

  public static function getNavigationBadge(): ?string
  {
    return static::getModel()::count();
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
              ->code(emp_code(new Employee, '01/YSD/'), generateable: false),
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
              ->default(today())
              ->maxDate(today()),
            DatePicker::make('exit_date')
              ->required()
              ->live()
              ->visible(fn(Get $get): bool => $get('status') === EmployeeStatus::RESIGN->value || $get('status') === EmployeeStatus::RETIRE->value),
            TextInput::make('ktp')
              ->numeric()
              ->unique(ignoreRecord: true)
              ->maxLength(255),
            PhoneInput::make('phone')
              ->unique(ignoreRecord: true)
              ->idDefaultFormat(),
          ]),
        Grid::make()
          ->columnSpan(1)
          ->schema([
            Section::make('Other Information')
              ->schema([
                ToggleButtons::make('status')
                  ->required()
                  ->inline()
                  ->live()
                  ->options(EmployeeStatus::class),
                ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
                Select::make('role')
                  ->required()
                  ->live()
                  ->options(EmployeeRole::class)
              ])
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\ImageColumn::make('photo')
          ->circular(),
        Tables\Columns\IconColumn::make('is_user_assigned')
          ->label('Is User Assigned')
          ->state(fn(Employee $record) => $record->employable()->exists())
          ->tooltip(fn(Employee $record) => $record->employable?->name ?? 'Assign User')
          ->boolean()
          ->alignCenter()
          ->action(static::getAssignUserAction()),
        Tables\Columns\TextColumn::make('code')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->description(fn(Employee $record): string => $record->alias ?? null)
          ->searchable(),
        Tables\Columns\TextColumn::make('join_date')
          ->label('Tanggal Masuk')
          ->date()
          ->sortable(),
        Tables\Columns\TextColumn::make('ktp')
          ->label('No KTP')
          ->limit(7, '***')
          ->searchable()
          ->placeholder('No data'),
        Tables\Columns\TextColumn::make('gender')
          ->badge(),
        Tables\Columns\TextColumn::make('role')
          ->badge(),
        Tables\Columns\TextColumn::make('status')
          ->badge(),
        Tables\Columns\TextColumn::make('working_day')
          ->label('Masa Kerja (Hari)')
          ->numeric()
          ->state(function (Employee $record): string {
            return today()->diffInDays($record->join_date);
          })
          ->sortable(query: function (Builder $query, string $direction): Builder {
            return $query->orderBy('join_date', $direction);
          }),
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
          static::getAssignUserAction()
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
    return Action::make('assign_user')
      ->icon(UserResource::getNavigationIcon())
      ->label('Assign User')
      ->color('info')
      ->form([
        Select::make('user_id')
          ->required()
          ->hiddenLabel()
          ->default(fn(Employee|TourLeader $record) => $record->employable?->id)
          ->options(User::pluck('name', 'id')),
      ])
      ->action(function (array $data, Employee|TourLeader $record): void {
        if ($record->employable()->exists()) {
          $record->employable()->update([
            'employable_id' => null,
            'employable_type' => null
          ]);
        }
        $user = User::findOrFail($data['user_id']);
        $record->employable()->save($user);
        Notification::make()
          ->success()
          ->title('Success')
          ->body("User assigned for <strong>{$record->name}</strong>")
          ->send();
      });
  }
}
