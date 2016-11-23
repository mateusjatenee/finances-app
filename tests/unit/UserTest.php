<?php

use App\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function it_calculates_the_total_expended_in_a_month()
    {
        $user = $this->createUser();

        $expenses = factory(Expense::class, 3)->make(['value' => 30]);

        $fourth_expense = factory(Expense::class)->make(['date' => Carbon::today()->subDays(35)]);

        $user->expenses()->saveMany($expenses);
        $user->expenses()->save($fourth_expense);

        $this->assertEquals(90, $user->totalExpendedInMonth());
    }
}
