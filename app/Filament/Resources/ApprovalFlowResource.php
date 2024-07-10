<?php

namespace EightyNine\Approvals\Filament\Resources;

use App\Enums\NavigationGroupLabel;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Models\ApprovalFlow;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use EightyNine\Approvals\Services\ModelScannerService;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use EightyNine\Approvals\Filament\Resources\ApprovalFlowResource\Pages;
use EightyNine\Approvals\Filament\Resources\ApprovalFlowResource\RelationManagers;
use EightyNine\Approvals\Filament\Resources\ApprovalFlowResource\RelationManagers\StepsRelationManager;

class ApprovalFlowResource extends Resource
{
  protected static ?string $model = ProcessApprovalFlow::class;

  protected static ?string $modelLabel = 'Approval Flow';

  protected static ?string $pluralModelLabel = 'Approval Flows';

  public static function getNavigationIcon(): ?string
  {
    return config('approvals.navigation.icon', 'heroicon-o-clipboard-document-check');
  }

  public static function shouldRegisterNavigation(): bool
  {
    return config('approvals.navigation.should_register_navigation', true);
  }

  public static function getLabel(): string
  {
    return __('filament-approvals::approvals.navigation.label');
  }

  public static function getNavigationGroup(): ?string
  {
    return NavigationGroupLabel::SETTING->getLabel();
  }

  public static function getNavigationSort(): ?int
  {
    return config('approvals.navigation.sort', 1);
  }

  public static function getPluralLabel(): string
  {
    return __('filament-approvals::approvals.navigation.plural_label');
  }

  public static function form(Form $form): Form
  {
    $models = (new ModelScannerService())->getApprovableModels();

    return $form
      ->columns(12)
      ->schema([
        TextInput::make('name')
          ->columnSpan(fn($context) => $context === 'create' ? 12 : 6)
          ->required(),
        Select::make('approvable_type')
          ->columnSpan(fn($context) => $context === 'create' ? 12 : 6)
          ->options(function () use ($models) {
            $models = array_map(function ($model) {
              $modelName = str_replace('App\Models\\', '', $model);
              return Str::headline($modelName);
            }, $models);
            return $models;
          })
          ->required(),
        Forms\Components\Placeholder::make('warning')
          ->visible(fn() => blank($models))
          ->columnSpanFull()
          ->content(new HtmlString('No models in <b>App\Models</b> extend the <b>ApprovableModel</b>. Please see our documentation.'))
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        TextColumn::make('name'),
        TextColumn::make('approvable_type'),
        TextColumn::make('created_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
        TextColumn::make('updated_at')
          ->dateTime()
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      StepsRelationManager::class
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ListApprovalFlows::route('/'),
      // 'create' => Pages\CreateApprovalFlow::route('/create'),
      'edit' => Pages\EditApprovalFlow::route('/{record}/edit'),
    ];
  }
}
