<?php

declare(strict_types = 1);

use App\Enums\{TransactionMethod, TransactionType};
use App\Filament\Resources\RecurringExpenses\Pages\{CreateRecurringExpense, EditRecurringExpense, ListRecurringExpenses};
use App\Models\{Account, Category, RecurringExpense, User};
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

describe('listing', function (): void {
    it('lists only the authenticated user recurring expenses', function (): void {
        $user      = User::factory()->create()->fresh();
        $otherUser = User::factory()->create()->fresh();

        $userRecurringExpense = RecurringExpense::factory()->for($user)->create()->fresh();
        RecurringExpense::factory()->for($otherUser)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(ListRecurringExpenses::class)
            ->assertCanSeeTableRecords([$userRecurringExpense])
            ->assertCanNotSeeTableRecords([RecurringExpense::where('user_id', $otherUser->id)->first()]);
    });

    it('still shows the account name on the table after the account is soft deleted', function (): void {
        $user             = User::factory()->create()->fresh();
        $account          = Account::factory()->for($user)->create(['name' => 'Old Wallet'])->fresh();
        $recurringExpense = RecurringExpense::factory()->for($user)->create(['account_id' => $account->id])->fresh();

        $account->delete();

        $this->actingAs($user);

        Livewire::test(ListRecurringExpenses::class)
            ->assertCanSeeTableRecords([$recurringExpense])
            ->assertTableColumnStateSet('account.name', 'Old Wallet', $recurringExpense);
    });

    it('still shows the category name on the table after the category is soft deleted', function (): void {
        $user             = User::factory()->create()->fresh();
        $category         = Category::factory()->for($user)->create(['name' => 'Old Groceries'])->fresh();
        $recurringExpense = RecurringExpense::factory()->for($user)->create(['category_id' => $category->id])->fresh();

        $category->delete();

        $this->actingAs($user);

        Livewire::test(ListRecurringExpenses::class)
            ->assertCanSeeTableRecords([$recurringExpense])
            ->assertTableColumnStateSet('category.name', 'Old Groceries', $recurringExpense);
    });
});

describe('creating', function (): void {
    it('creates a recurring expense and assigns it to the authenticated user as an expense', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::PIX->value,
                'amount'       => '200.00',
                'day_of_month' => 10,
                'is_active'    => true,
                'description'  => 'Monthly subscription.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $recurringExpense = RecurringExpense::where('user_id', $user->id)->first();

        expect($recurringExpense)->not->toBeNull()
            ->and($recurringExpense->account_id)->toBe($account->id)
            ->and($recurringExpense->category_id)->toBe($category->id)
            ->and($recurringExpense->amount)->toBe('200.00')
            ->and($recurringExpense->day_of_month)->toBe(10)
            ->and($recurringExpense->type)->toBe(TransactionType::EXPENSE);
    });

    it('requires account_id to create a recurring expense', function (): void {
        $user     = User::factory()->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => null,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::PIX->value,
                'amount'       => '100.00',
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['account_id' => 'required']);
    });

    it('requires category_id to create a recurring expense', function (): void {
        $user    = User::factory()->create()->fresh();
        $account = Account::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => null,
                'method'       => TransactionMethod::PIX->value,
                'amount'       => '100.00',
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['category_id' => 'required']);
    });

    it('requires method to create a recurring expense', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => null,
                'amount'       => '100.00',
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['method' => 'required']);
    });

    it('requires amount to create a recurring expense', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::CASH->value,
                'amount'       => null,
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['amount' => 'required']);
    });

    it('requires day_of_month to create a recurring expense', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::CASH->value,
                'amount'       => '100.00',
                'day_of_month' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['day_of_month' => 'required']);
    });

    it('rejects a day_of_month below 1', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::CASH->value,
                'amount'       => '100.00',
                'day_of_month' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors(['day_of_month']);
    });

    it('rejects a day_of_month above 31', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::CASH->value,
                'amount'       => '100.00',
                'day_of_month' => 32,
            ])
            ->call('create')
            ->assertHasFormErrors(['day_of_month']);
    });

    it('rejects an amount below the minimum', function (): void {
        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::CASH->value,
                'amount'       => '0,00',
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasFormErrors(['amount']);
    });

    it('accepts amount using BRL locale decimal format', function (): void {
        App::setLocale('pt_BR');

        $user     = User::factory()->create()->fresh();
        $account  = Account::factory()->for($user)->create()->fresh();
        $category = Category::factory()->for($user)->create()->fresh();

        $this->actingAs($user);

        Livewire::test(CreateRecurringExpense::class)
            ->fillForm([
                'account_id'   => $account->id,
                'category_id'  => $category->id,
                'method'       => TransactionMethod::PIX->value,
                'amount'       => '200,50',
                'day_of_month' => 5,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(
            RecurringExpense::where('user_id', $user->id)->first()->amount
        )->toBe('200.50');
    });
});

describe('editing', function (): void {
    it('keeps the soft-deleted account bound on the edit form', function (): void {
        $user             = User::factory()->create()->fresh();
        $account          = Account::factory()->for($user)->create(['name' => 'Old Wallet'])->fresh();
        $recurringExpense = RecurringExpense::factory()->for($user)->create(['account_id' => $account->id])->fresh();

        $account->delete();

        $this->actingAs($user);

        Livewire::test(EditRecurringExpense::class, ['record' => $recurringExpense->getRouteKey()])
            ->assertSchemaStateSet(['account_id' => $account->id]);
    });

    it('keeps the soft-deleted category bound on the edit form', function (): void {
        $user             = User::factory()->create()->fresh();
        $category         = Category::factory()->for($user)->create(['name' => 'Old Groceries'])->fresh();
        $recurringExpense = RecurringExpense::factory()->for($user)->create(['category_id' => $category->id])->fresh();

        $category->delete();

        $this->actingAs($user);

        Livewire::test(EditRecurringExpense::class, ['record' => $recurringExpense->getRouteKey()])
            ->assertSchemaStateSet(['category_id' => $category->id]);
    });

    it('updates an existing recurring expense', function (): void {
        $user        = User::factory()->create()->fresh();
        $account     = Account::factory()->for($user)->create()->fresh();
        $newAccount  = Account::factory()->for($user)->create()->fresh();
        $category    = Category::factory()->for($user)->create()->fresh();
        $newCategory = Category::factory()->for($user)->create()->fresh();

        $recurringExpense = RecurringExpense::factory()->for($user)->create([
            'account_id'   => $account->id,
            'category_id'  => $category->id,
            'method'       => TransactionMethod::PIX->value,
            'amount'       => '100.00',
            'day_of_month' => 1,
            'description'  => 'Old description.',
        ])->fresh();

        $this->actingAs($user);

        Livewire::test(EditRecurringExpense::class, ['record' => $recurringExpense->getRouteKey()])
            ->fillForm([
                'account_id'   => $newAccount->id,
                'category_id'  => $newCategory->id,
                'method'       => TransactionMethod::CREDIT->value,
                'amount'       => '500.00',
                'day_of_month' => 20,
                'description'  => 'Updated description.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $fresh = RecurringExpense::find($recurringExpense->id);

        expect($fresh->account_id)->toBe($newAccount->id)
            ->and($fresh->category_id)->toBe($newCategory->id)
            ->and($fresh->method)->toBe(TransactionMethod::CREDIT)
            ->and($fresh->amount)->toBe('500.00')
            ->and($fresh->day_of_month)->toBe(20)
            ->and($fresh->description)->toBe('Updated description.');
    });
});

describe('authorization', function (): void {
    it('redirects unauthenticated users to the login page', function (): void {
        $this->get(route('filament.admin.resources.recurring-expenses.index'))
            ->assertRedirect(route('filament.admin.auth.login'));
    });

    it('returns 404 when accessing another user recurring expense on the edit page', function (): void {
        $user                  = User::factory()->create()->fresh();
        $otherUser             = User::factory()->create()->fresh();
        $otherRecurringExpense = RecurringExpense::factory()->for($otherUser)->create()->fresh();

        $this->actingAs($user);

        $this->get(route('filament.admin.resources.recurring-expenses.edit', ['record' => $otherRecurringExpense->getRouteKey()]))
            ->assertNotFound();
    });
});
