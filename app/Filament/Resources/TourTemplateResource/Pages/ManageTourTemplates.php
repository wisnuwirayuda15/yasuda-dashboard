<?php

namespace App\Filament\Resources\TourTemplateResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\TourTemplateResource;
use EightyNine\Approvals\Models\ApprovableModel;

class ManageTourTemplates extends ManageRecords
{
  protected static string $resource = TourTemplateResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->using(function (array $data): Model {
          $model = static::getModel()::create($data);
          
          instant_approval($model, $data);
      
          return $model;
        })
    ];
  }

  public function setPage($page, $pageName = 'page'): void
  {
    parent::setPage($page, $pageName);
    $this->dispatch(\App\Enums\JavascriptEvent::SCROLL_TO_TOP->value);
  }
}
