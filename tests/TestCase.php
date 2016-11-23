<?php

use App\Exceptions\Handler;
use App\Expense;
use App\User;
use \Exception;
use \Mockery as m;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(Handler::class, new class extends Handler
        {
            public function __construct()
            {
            }
            public function report(Exception $e)
            {
            }
            public function render($request, Exception $e)
            {
                throw $e;
            }
        });
    }

    public function tearDown()
    {
        m::close();
    }

    protected function mock($class)
    {
        $mock = m::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }

    public function createUser()
    {
        return factory(User::class)->create();
    }

    public function createExpense(User $user)
    {
        return $user->expenses()->save(factory(Expense::class)->make());
    }
}
