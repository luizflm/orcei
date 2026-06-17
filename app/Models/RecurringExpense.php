<?php

declare(strict_types = 1);

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\{TransactionMethod, TransactionType};
use Database\Factories\RecurringExpenseFactory;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $account_id
 * @property-read int $category_id
 * @property-read TransactionMethod $method
 * @property-read TransactionType $type
 * @property-read string $amount
 * @property-read ?string $description
 * @property-read int $day_of_month
 * @property-read bool $is_active
 * @property-read ?\Illuminate\Support\Carbon $last_generated_at
 * @property-read \Illuminate\Support\Carbon $created_at
 * @property-read \Illuminate\Support\Carbon $updated_at
 */
class RecurringExpense extends Model
{
    /** @use HasFactory<RecurringExpenseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'method'            => TransactionMethod::class,
            'type'              => TransactionType::class,
            'amount'            => MoneyCast::class,
            'is_active'         => 'boolean',
            'last_generated_at' => 'date',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
