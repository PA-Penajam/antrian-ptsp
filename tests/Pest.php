<?php

$testConnection = $_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? null;
$testDatabase = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? null;

if (in_array($testConnection, ['mysql', 'mariadb', 'pgsql', 'sqlsrv'], true)
    && ! str_ends_with((string) $testDatabase, '_test')
    && ! str_ends_with((string) $testDatabase, '_testing')) {
    throw new \RuntimeException(
        sprintf(
            'Unsafe testing database "%s" for connection "%s". Use SQLite or a dedicated *_test database.',
            (string) $testDatabase,
            (string) $testConnection,
        )
    );
}

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
