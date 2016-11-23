<?php

namespace App\Http\Controllers\Api;

use App\Expense;
use App\Http\Controllers\Controller;
use App\Transformers\ExpenseTransformer;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Contracts\Validation\Factory as Validator;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    protected $auth;

    protected $validator;

    protected $expense;

    public function __construct(Auth $auth, Validator $validator, Expense $expense)
    {
        $this->auth = $auth;
        $this->validator = $validator;
        $this->expense = $expense;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = $this->auth->user();

        $start = request('start');
        $end = request('end');

        $expenses = $user->expenses()
            ->when($start, function ($query) use ($start) {
                return $query->where('date', '>', $start);
            })
            ->when($end, function ($query) use ($end) {
                return $query->where('date', '<', $end);
            })
            ->get();

        return fractal()
            ->collection($expenses)
            ->transformWith(new ExpenseTransformer)
            ->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $this->auth->user();

        $validator = $this->validateRequest($request->all());

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $expense = $user->expenses()->create($request->all());

        return fractal()
            ->item($expense)
            ->transformWith(new ExpenseTransformer)
            ->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = $this->auth->user();

        $expense = $this->expense->find($id);

        if ($user->cant('see', $expense)) {
            return $this->actionAuthorizationError();
        }

        return fractal()
            ->item($expense)
            ->transformWith(new ExpenseTransformer)
            ->toArray();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = $this->auth->user();

        $expense = $this->expense->find($id);

        if ($user->cant('update', $expense)) {
            return $this->actionAuthorizationError();
        }

        $expense->update($request->all());

        return fractal()
            ->item($expense)
            ->transformWith(new ExpenseTransformer)
            ->toArray();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = $this->auth->user();

        $expense = $this->expense->find($id);

        if ($user->cant('update', $expense)) {
            return $this->actionAuthorizationError();
        }

        $expense->delete();

        return response()->json([], 204);
    }

    protected function validateRequest(array $fields)
    {
        return $this->validator->make($fields, [
            'title' => 'required|string',
            'value' => 'required',
            'location' => 'required|string',
            'date' => 'required|date',
        ]);
    }
}
