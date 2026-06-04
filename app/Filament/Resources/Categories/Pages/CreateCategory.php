<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Categories\Pages;

use App\Actions\Categories\CreateCategory as CreateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $action = app(CreateCategoryAction::class);

        return $action($data, auth()->id());
    }
}
