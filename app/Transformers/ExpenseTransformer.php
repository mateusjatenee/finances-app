<?php

namespace App\Transformers;

use App\Expense;
use League\Fractal\TransformerAbstract;

class ExpenseTransformer extends TransformerAbstract
{
    public function transform(Expense $expense)
    {
        return [
            'title' => $expense->title,
            'value' => (float) $expense->value,
            'location' => $expense->location,
            'date' => $expense->date->format('Y-m-d'),
            'readable_date' => $expense->date->diffForHumans(),
        ];
    }
}
