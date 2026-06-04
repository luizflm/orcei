<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Categories\Pages;

use App\Actions\Categories\UpdateCategory as UpdateCategoryAction;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Category $record */
        $action = app(UpdateCategoryAction::class);

        return $action($record, $data);
    }
}
