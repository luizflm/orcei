<?php

declare(strict_types = 1);

namespace App\Actions\Categories;

use App\Models\Category;

class CreateCategory
{
    public function __invoke(array $data, int $userId): Category
    {
        /** @var Category $category */
        $category = Category::create([
            ...$data,
            'user_id' => $userId,
        ]);

        return $category;
    }
}
