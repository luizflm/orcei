<?php

declare(strict_types = 1);

namespace App\Actions\Categories;

use App\Models\Category;

class UpdateCategory
{
    public function __invoke(Category $category, array $data): Category
    {
        $category->update(['name' => $data['name'], 'color' => $data['color']]);

        return $category->fresh();
    }
}
