<?php

declare(strict_types = 1);

use App\Enums\{TransactionMethod, TransactionType};
use App\Filament\Resources\Transactions\Pages\{CreateTransaction, EditTransaction, ListTransactions};
use App\Models\{Account, Category, Transaction, User};
use Livewire\Livewire;

it('lists only the authenticated user transactions', function (): void {
    $user      = User::factory()->create()->fresh();
    $otherUser = User::factory()->create()->fresh();

    $userTransaction = Transaction::factory()->for($user)->create()->fresh();
    Transaction::factory()->for($otherUser)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->assertCanSeeTableRecords([$userTransaction])
        ->assertCanNotSeeTableRecords([Transaction::where('user_id', $otherUser->id)->first()]);
});

it('creates a transaction and assigns it to the authenticated user', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => TransactionMethod::PIX->value,
            'amount'      => '200.00',
            'description' => 'Lunch.',
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(
        Transaction::where('user_id', $user->id)
            ->where('account_id', $account->id)
            ->where('amount', 200.00)
            ->exists()
    )->toBeTrue();
});

it('requires account_id to create a transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => null,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => TransactionMethod::PIX->value,
            'amount'      => '100.00',
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['account_id' => 'required']);
});

it('requires category_id to create a transaction', function (): void {
    $user    = User::factory()->create()->fresh();
    $account = Account::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => null,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => TransactionMethod::PIX->value,
            'amount'      => '100.00',
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['category_id' => 'required']);
});

it('requires type to create a transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => null,
            'method'      => TransactionMethod::PIX->value,
            'amount'      => '100.00',
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['type' => 'required']);
});

it('requires method to create a transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => null,
            'amount'      => '100.00',
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['method' => 'required']);
});

it('requires amount to create a transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => TransactionMethod::CASH->value,
            'amount'      => null,
            'date'        => '2026-05-01',
        ])
        ->call('create')
        ->assertHasFormErrors(['amount' => 'required']);
});

it('requires date to create a transaction', function (): void {
    $user     = User::factory()->create()->fresh();
    $account  = Account::factory()->for($user)->create()->fresh();
    $category = Category::factory()->for($user)->create()->fresh();

    $this->actingAs($user);

    Livewire::test(CreateTransaction::class)
        ->fillForm([
            'account_id'  => $account->id,
            'category_id' => $category->id,
            'type'        => TransactionType::EXPENSE->value,
            'method'      => TransactionMethod::CASH->value,
            'amount'      => '100.00',
            'date'        => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['date' => 'required']);
});

it('updates an existing transaction', function (): void {
    $user        = User::factory()->create()->fresh();
    $account     = Account::factory()->for($user)->create()->fresh();
    $newAccount  = Account::factory()->for($user)->create()->fresh();
    $category    = Category::factory()->for($user)->create()->fresh();
    $newCategory = Category::factory()->for($user)->create()->fresh();

    $transaction = Transaction::factory()->for($user)->create([
        'account_id'  => $account->id,
        'category_id' => $category->id,
        'type'        => TransactionType::EXPENSE->value,
        'method'      => TransactionMethod::PIX->value,
        'amount'      => '100.00',
        'description' => 'Old description.',
        'date'        => '2026-01-01',
    ])->fresh();

    $this->actingAs($user);

    Livewire::test(EditTransaction::class, ['record' => $transaction->getRouteKey()])
        ->fillForm([
            'account_id'  => $newAccount->id,
            'category_id' => $newCategory->id,
            'type'        => TransactionType::INCOME->value,
            'method'      => TransactionMethod::CREDIT->value,
            'amount'      => '500.00',
            'description' => 'Updated description.',
            'date'        => '2026-05-01',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $fresh = Transaction::find($transaction->id);

    expect($fresh->account_id)->toBe($newAccount->id)
        ->and($fresh->category_id)->toBe($newCategory->id)
        ->and($fresh->amount)->toBe('500.00')
        ->and($fresh->description)->toBe('Updated description.');
});

it('filters transactions by type', function (): void {
    $user = User::factory()->create()->fresh();

    $income  = Transaction::factory()->for($user)->create(['type' => TransactionType::INCOME->value])->fresh();
    $expense = Transaction::factory()->for($user)->create(['type' => TransactionType::EXPENSE->value])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('type', TransactionType::INCOME->value)
        ->assertCanSeeTableRecords([$income])
        ->assertCanNotSeeTableRecords([$expense]);
});

it('filters transactions by method', function (): void {
    $user = User::factory()->create()->fresh();

    $pix  = Transaction::factory()->for($user)->create(['method' => TransactionMethod::PIX->value])->fresh();
    $cash = Transaction::factory()->for($user)->create(['method' => TransactionMethod::CASH->value])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('method', TransactionMethod::PIX->value)
        ->assertCanSeeTableRecords([$pix])
        ->assertCanNotSeeTableRecords([$cash]);
});

it('filters transactions by account', function (): void {
    $user         = User::factory()->create()->fresh();
    $account      = Account::factory()->for($user)->create()->fresh();
    $otherAccount = Account::factory()->for($user)->create()->fresh();

    $matchingTransaction = Transaction::factory()->for($user)->create(['account_id' => $account->id])->fresh();
    $otherTransaction    = Transaction::factory()->for($user)->create(['account_id' => $otherAccount->id])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('account', $account->id)
        ->assertCanSeeTableRecords([$matchingTransaction])
        ->assertCanNotSeeTableRecords([$otherTransaction]);
});

it('filters transactions by category', function (): void {
    $user          = User::factory()->create()->fresh();
    $category      = Category::factory()->for($user)->create()->fresh();
    $otherCategory = Category::factory()->for($user)->create()->fresh();

    $matchingTransaction = Transaction::factory()->for($user)->create(['category_id' => $category->id])->fresh();
    $otherTransaction    = Transaction::factory()->for($user)->create(['category_id' => $otherCategory->id])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('category', $category->id)
        ->assertCanSeeTableRecords([$matchingTransaction])
        ->assertCanNotSeeTableRecords([$otherTransaction]);
});

it('filters transactions by date range', function (): void {
    $user = User::factory()->create()->fresh();

    $inside = Transaction::factory()->for($user)->create(['date' => '2026-03-15'])->fresh();
    $before = Transaction::factory()->for($user)->create(['date' => '2026-02-28'])->fresh();
    $after  = Transaction::factory()->for($user)->create(['date' => '2026-04-01'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => '2026-03-01', 'until' => '2026-03-31'])
        ->assertCanSeeTableRecords([$inside])
        ->assertCanNotSeeTableRecords([$before, $after]);
});

it('filters transactions from a start date with no end date', function (): void {
    $user = User::factory()->create()->fresh();

    $recent = Transaction::factory()->for($user)->create(['date' => '2026-04-01'])->fresh();
    $old    = Transaction::factory()->for($user)->create(['date' => '2026-01-01'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => '2026-03-01', 'until' => null])
        ->assertCanSeeTableRecords([$recent])
        ->assertCanNotSeeTableRecords([$old]);
});

it('filters transactions until an end date with no start date', function (): void {
    $user = User::factory()->create()->fresh();

    $old    = Transaction::factory()->for($user)->create(['date' => '2026-01-01'])->fresh();
    $recent = Transaction::factory()->for($user)->create(['date' => '2026-04-01'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => null, 'until' => '2026-02-28'])
        ->assertCanSeeTableRecords([$old])
        ->assertCanNotSeeTableRecords([$recent]);
});

it('does not apply the from filter when it exceeds today', function (): void {
    $user = User::factory()->create()->fresh();

    $todayTransaction     = Transaction::factory()->for($user)->create(['date' => now()->toDateString()])->fresh();
    $yesterdayTransaction = Transaction::factory()->for($user)->create(['date' => now()->subDay()->toDateString()])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => now()->addMonth()->toDateString(), 'until' => null])
        ->assertCanSeeTableRecords([$todayTransaction, $yesterdayTransaction]);
});

it('does not apply the from filter when it exceeds the until date', function (): void {
    $user = User::factory()->create()->fresh();

    $beforeUntil = Transaction::factory()->for($user)->create(['date' => '2026-04-01'])->fresh();
    $afterFrom   = Transaction::factory()->for($user)->create(['date' => '2026-05-15'])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => '2026-05-10', 'until' => '2026-05-01'])
        ->assertCanSeeTableRecords([$beforeUntil])
        ->assertCanNotSeeTableRecords([$afterFrom]);
});

it('clamps the until filter to today when a future date is provided', function (): void {
    $user = User::factory()->create()->fresh();

    $futureTransaction  = Transaction::factory()->for($user)->create(['date' => now()->addDay()->toDateString()])->fresh();
    $presentTransaction = Transaction::factory()->for($user)->create(['date' => now()->toDateString()])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => now()->toDateString(), 'until' => now()->addMonth()->toDateString()])
        ->assertCanSeeTableRecords([$presentTransaction])
        ->assertCanNotSeeTableRecords([$futureTransaction]);
});

it('does not apply either date filter when both from and until are future dates', function (): void {
    $user = User::factory()->create()->fresh();

    $pastTransaction   = Transaction::factory()->for($user)->create(['date' => now()->subDay()->toDateString()])->fresh();
    $futureTransaction = Transaction::factory()->for($user)->create(['date' => now()->addDay()->toDateString()])->fresh();

    $this->actingAs($user);

    Livewire::test(ListTransactions::class)
        ->filterTable('date', ['from' => now()->addWeek()->toDateString(), 'until' => now()->addMonth()->toDateString()])
        ->assertCanSeeTableRecords([$pastTransaction])
        ->assertCanNotSeeTableRecords([$futureTransaction]);
});

it('redirects unauthenticated users to the login page', function (): void {
    $this->get(route('filament.admin.resources.transactions.index'))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('returns 404 when accessing another user transaction on the edit page', function (): void {
    $user             = User::factory()->create()->fresh();
    $otherUser        = User::factory()->create()->fresh();
    $otherTransaction = Transaction::factory()->for($otherUser)->create()->fresh();

    $this->actingAs($user);

    $this->get(route('filament.admin.resources.transactions.edit', ['record' => $otherTransaction->getRouteKey()]))
        ->assertNotFound();
});
