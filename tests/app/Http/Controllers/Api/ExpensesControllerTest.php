<?php

use App\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExpensesControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_lists_a_user_expenses_of_the_month()
    {
        $this->disableExceptionHandling();

        $user = $this->createUser();

        $expenses = factory(Expense::class, 2)->make();

        $user->expenses()->saveMany($expenses);

        $this
            ->actingAs($user)
            ->get(route('api::expenses.index'));

        foreach ($expenses as $expense) {
            $this->seeJson([
                'title' => $expense->title,
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }

    }

    /** @test */
    public function it_lists_a_user_expenses_at_the_given_interval()
    {
        $this->disableExceptionHandling();

        $user = $this->createUser();

        $expenses = factory(Expense::class, 2)->make();

        $third_expense = factory(Expense::class)->make([
            'date' => Carbon::yesterday()->subWeeks(2),
        ]);

        $fourth_expense = factory(Expense::class)->make([
            'date' => Carbon::today()->addDays(3),
        ]);

        $should_not_be_seen_expenses = [$third_expense, $fourth_expense];

        $user->expenses()->saveMany($expenses);
        $user->expenses()->saveMany($should_not_be_seen_expenses);

        $this
            ->actingAs($user)
            ->get(route('api::expenses.index', [
                'start' => Carbon::yesterday()->format('Y-m-d'), 'end' => Carbon::tomorrow()->format('Y-m-d'),
            ]));

        foreach ($expenses as $expense) {
            $this->seeJson([
                'title' => $expense->title,
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }

        foreach ($should_not_be_seen_expenses as $expense) {
            $this->dontSeeJson([
                'title' => $expense->title,
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }
    }

    /** @test */
    public function it_creates_an_expense()
    {
        $user = $this->createUser();

        $date = Carbon::today();

        $this
            ->actingAs($user)
            ->post(route('api::expenses.store'), [
                'title' => 'Foo',
                'value' => 35,
                'location' => 'Home',
                'date' => $date->format('Y-m-d'),
            ])
            ->seeJson([
                'title' => 'Foo',
                'value' => 35,
                'location' => 'Home',
                'date' => $date->format('Y-m-d'),
                'readable_date' => $date->diffForHumans(),
            ]);
    }

    /** @test */
    public function it_returns_validation_errors_when_creating_an_expense()
    {
        $user = $this->createUser();

        $date = Carbon::today();

        $this
            ->actingAs($user)
            ->post(route('api::expenses.store'), [
                'value' => 35,
                'location' => 'Home',
                'date' => $date->format('Y-m-d'),
            ])
            ->seeStatusCode(422)
            ->seeJson([
                'success' => false,
                'errors' => [
                    'title' => [
                        'The title field is required.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_edits_an_expense()
    {
        $user = $this->createUser();

        $expense = $this->createExpense($user);

        $date = Carbon::today()->addDays(20);

        $this
            ->actingAs($user)
            ->put(route('api::expenses.update', $expense->id), [
                'title' => 'Foo',
                'location' => 'Foobar',
                'value' => 12,
                'date' => $date->format('Y-m-d'),
            ])
            ->seeStatusCode(200)
            ->seeJson([
                'title' => 'Foo',
                'location' => 'Foobar',
                'value' => 12,
                'date' => $date->format('Y-m-d'),
                'readable_date' => $date->diffForHumans(),
            ]);
    }

}
