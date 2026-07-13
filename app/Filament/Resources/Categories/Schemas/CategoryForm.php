<?php

declare(strict_types = 1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\{ColorPicker, TextInput};
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resource.category.field.name'))
                    ->required()
                    ->maxLength(100)
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule): Unique => $rule
                            ->where('user_id', auth()->id())
                            ->whereNull('deleted_at'),
                    )
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $deletedCategoryExists = Category::onlyTrashed()
                                ->where('user_id', auth()->id())
                                ->where('name', (string) $value)
                                ->exists();

                            if ($deletedCategoryExists) {
                                $fail(__('validation.category.name.deleted_exists'));
                            }
                        },
                    ]),
                ColorPicker::make('color')
                    ->label(__('resource.category.field.color'))
                    ->required()
                    ->suffixAction(
                        Action::make('generateRandomColor')
                            ->icon('heroicon-m-arrow-path')
                            ->action(function (Set $set): void {
                                $set('color', sprintf('#%06x', random_int(0, 0xFFFFFF)));
                            })
                    ),
            ]);
    }
}
