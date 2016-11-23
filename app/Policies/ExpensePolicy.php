<?php

namespace App\Policies;

use App\Expense;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function see(User $user, Expense $expense)
    {
        return $user->id == $expense->user_id;
    }

    public function update(User $user, Expense $expense)
    {
        return $user->id == $expense->user_id;
    }
}
