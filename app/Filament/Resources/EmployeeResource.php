<?php

namespace App\Filament\Resources;

use Filament\Forms;
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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
            FileUpload::make('image')
              ->image()
              ->imageEditor()
              ->maxSize(2048)
              ->directory('employee')
              ->imageCropAspectRatio('1:1')
              ->imageResizeMode('cover')
              ->columnSpanFull(),
            Select::make('user_id')
              ->unique(ignoreRecord: true)
              ->relationship('user', 'name'),
            TextInput::make('name')
              ->required(),
            TextInput::make('alias')
              ->required()
              ->unique(ignoreRecord: true),
            DatePicker::make('join_date')
              ->required()
              ->default(today())
              ->maxDate(today()),
            DatePicker::make('exit_date'),
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
                  ->options(EmployeeStatus::class),
                ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
                Select::make('role')
                  ->required()
                  ->live()
                  ->options(EmployeeRole::class)
                  // ->afterStateUpdated(function (Set $set, ?string $state) {
                  //   if ($state === EmployeeRole::TOUR_LEADER->value) {
                  //     $set('code', emp_code(new Employee, '02/TLF/'));
                  //   } else {
                  //     $set('code', emp_code(new Employee, '01/YSD/'));
                  //   }
                  // })
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
        Tables\Columns\TextColumn::make('code')
          ->badge()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->sortable()
          ->searchable(),
        Tables\Columns\TextColumn::make('alias')
          ->sortable()
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
}
