<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ExpensesControllerTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_lists_a_user_expenses_of_the_month()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class);

        $expenses = factory(Expense::class, 2)->make();

        $user->expenses()->saveMany($expenses);

        $this
            ->actingAs($user)
            ->get(route('api::expenses.index'));

        foreach ($expenses as $expense) {
            $this->seeJsonSubset([
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }

    }

    public function it_lists_a_user_expenses_at_the_given_interval()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class);

        $expenses = factory(Expense::class, 2)->make();

        $third_expense = factory(Expense::class)->make([
            'date' => Carbon::yesterday()->subWeeks(2),
        ]);

        $fourth_expense = factory(Expense::class)->make([
            'date' => Carbon::today()->addDays(3),
        ]);

        $should_not_be_seen_expenses = [$third_expense, $fourth_expense];

        $user->expenses()->saveMany($expenses);

        $this
            ->actingAs($user)
            ->get(route('api::expenses.index', [
                'start' => Carbon::yesterday()->format('Y-m-d'), 'end' => Carbon::tomorrow()->format('Y-m-d'),
            ]));

        foreach ($expenses as $expense) {
            $this->seeJsonSubset([
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }

        foreach ($should_not_be_seen_expenses as $expense) {
            $this->notSeeJson([
                'value' => (float) $expense->value,
                'location' => $expense->location,
                'date' => $expense->date->format('Y-m-d'),
                'readable_date' => $expense->date->diffForHumans(),
            ]);
        }

    }

}
