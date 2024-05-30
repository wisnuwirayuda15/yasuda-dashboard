<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Enums\Gender;
use Filament\Forms\Form;
use App\Models\TourLeader;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Illuminate\Database\Eloquent\Builder;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TourLeaderResource\Pages;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use App\Filament\Resources\TourLeaderResource\RelationManagers;

class TourLeaderResource extends Resource
{
  protected static ?string $model = TourLeader::class;

  protected static ?string $navigationIcon = 'gmdi-tour-r';

  protected static ?string $navigationGroup = NavigationGroupLabel::MASTER_DATA->value;

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Tour Leader')
          ->schema([
            Forms\Components\Select::make('user_id')
              ->relationship('user', 'name'),
            Forms\Components\TextInput::make('name')
              ->required()
              ->maxLength(255),
            Forms\Components\FileUpload::make('photo')
              ->required()
              ->image()
              ->imageEditor()
              ->maxSize(2048)
              ->directory('tour-leader')
              ->imageCropAspectRatio('1:1')
              ->imageResizeMode('cover'),
          ])
          ->columnSpan(2),
        Forms\Components\Grid::make()
          ->schema([
            Forms\Components\Section::make()
              ->schema([
                Forms\Components\ToggleButtons::make('gender')
                  ->required()
                  ->inline()
                  ->options(Gender::class),
              ]),
            Forms\Components\Section::make('Contact')
              ->schema([
                PhoneInput::make('phone')
                  ->required()
                  ->idDefaultFormat(),
              ]),
          ])->columnSpan(1)

      ])
      ->columns(3);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        // Tables\Columns\TextColumn::make('user.name')
        //   ->numeric()
        //   ->sortable(),
        Tables\Columns\ImageColumn::make('photo')
          ->circular()
          ->searchable(),
        Tables\Columns\TextColumn::make('name')
          ->searchable(),
        Tables\Columns\TextColumn::make('phone')
          ->searchable(),
        Tables\Columns\TextColumn::make('gender')
          ->searchable(),
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
        Tables\Actions\ActionGroup::make([
          Tables\Actions\ViewAction::make(),
          Tables\Actions\EditAction::make(),
          Tables\Actions\DeleteAction::make(),
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
      'index' => Pages\ListTourLeaders::route('/'),
      'create' => Pages\CreateTourLeader::route('/create'),
      'view' => Pages\ViewTourLeader::route('/{record}'),
      'edit' => Pages\EditTourLeader::route('/{record}/edit'),
    ];
  }
}
