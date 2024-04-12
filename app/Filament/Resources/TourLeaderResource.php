<?php

namespace App\Filament\Resources;

use App\Enums\Gender;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\TourLeader;
use Filament\Tables\Table;
use Filament\Resources\Resource;
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

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Tour Leader')
          ->schema([
            Forms\Components\Select::make('user_id')
              ->native(false)
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
                  ->focusNumberFormat(PhoneInputNumberType::E164)
                  ->defaultCountry('ID')
                  ->initialCountry('id')
                  ->showSelectedDialCode(true)
                  ->formatAsYouType(false)
                  ->required()
                  ->rules('phone:mobile'),
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
        Tables\Filters\TrashedFilter::make(),
      ])
      ->actions([
        Tables\Actions\ViewAction::make(),
        Tables\Actions\EditAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
          Tables\Actions\ForceDeleteBulkAction::make(),
          Tables\Actions\RestoreBulkAction::make(),
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

  public static function getEloquentQuery(): Builder
  {
    return parent::getEloquentQuery()
      ->withoutGlobalScopes([
        SoftDeletingScope::class,
      ]);
  }
}
