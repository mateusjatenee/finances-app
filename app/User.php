<?php

namespace App;

use App\Expense;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function totalExpendedInMonth()
    {
        $beggining = Carbon::today()->startOfMonth();
        $end = Carbon::today()->endOfMonth();

        return $this->expenses->where('date', '<', $end)->where('date', '>', $beggining)->sum('value');
    }
}
