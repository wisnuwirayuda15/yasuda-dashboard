<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Regency;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Destination;
use App\Models\TourTemplate;
use Filament\Resources\Resource;
use App\Enums\NavigationGroupLabel;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use EightyNine\Approvals\Tables\Actions\RejectAction;
use EightyNine\Approvals\Tables\Actions\SubmitAction;
use App\Filament\Resources\TourTemplateResource\Pages;
use EightyNine\Approvals\Tables\Actions\ApproveAction;
use EightyNine\Approvals\Tables\Actions\DiscardAction;
use EightyNine\Approvals\Tables\Actions\ApprovalActions;
use EightyNine\Approvals\Tables\Columns\ApprovalStatusColumn;
use App\Filament\Resources\TourTemplateResource\RelationManagers;

class TourTemplateResource extends Resource
{
  protected static ?string $model = TourTemplate::class;

  protected static ?string $navigationIcon = 'fontisto-holiday-village';

  public static function getLabel(): string
  {
    return __('navigation.label.' . static::getSlug());
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::MASTER_DATA->getLabel();
  }

  public static function form(Form $form): Form
  {
    return $form
      ->schema([
        TextInput::make('name')
          ->required()
          ->live(true)
          ->helperText('You can generate name based on selected regency and destinations.')
          ->maxLength(255)
          ->hintAction(
            Action::make('generate_name')
              ->disabled(fn(Get $get) => blank($get('regency_id')) || blank($get('destinations')))
              ->action(function (Get $get, Set $set, TextInput $component) {
                $regency = Regency::findOrFail($get('regency_id'));
                $destinations = Destination::findOrFail($get('destinations'));
                if (blank($regency) || blank($destinations)) {
                  $set($component, null);
                } else {
                  $set($component, "{$regency->name} ({$destinations->implode('name', ' + ')})");
                }
              })
          ),
        Select::make('destinations')
          ->required()
          ->live(true)
          ->multiple()
          ->options(Destination::pluck('name', 'id')),
        Select::make('regency_id')
          ->required()
          ->live(true)
          ->relationship('regency', 'name'),
        FileUpload::make('image')
          ->image()
          ->imageEditor()
          ->maxSize(2048)
          ->directory('tour-template')
          ->imageCropAspectRatio('1:1')
          ->imageResizeMode('cover')
          ->columnSpanFull(),
        RichEditor::make('description')->columnSpanFull(),
        Checkbox::make('submission')->submission()
      ])->columns(1);
  }

  public static function table(Table $table): Table
  {
    $destination = Destination::all();

    return $table
      ->columns([
        TextColumn::make('name')
          ->searchable(),
        TextColumn::make('regency.name')
          ->searchable()
          ->sortable(),
        TextColumn::make('destinations')
          ->badge()
          ->searchable()
          ->formatStateUsing(fn(string $state): string => $destination->find($state)->name),
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
      ->filters([
        Filter::make('approved')->approved(),
        Filter::make('notApproved')->notApproved(),
      ])
      ->actions(
        [
          SubmitAction::make()->color('info'),
          ApproveAction::make()->color('success'),
          DiscardAction::make()->color('warning'),
          RejectAction::make()->color('danger'),
          ActionGroup::make([
            ViewAction::make(),
            EditAction::make(),
            DeleteAction::make(),
          ])->visible(fn(Model $record) => $record->isApprovalCompleted()),
        ]
      )
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ]);
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageTourTemplates::route('/'),
    ];
  }
}
