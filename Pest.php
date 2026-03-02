<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses()->beforeAll(function () {
    // This runs once before all tests
})->in('Feature');

uses()->beforeEach(function () {
    // This runs before each test
})->in('Unit', 'Feature');

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

function loadEnv(): void
{
    $envFile = __DIR__ . '/.env.testing';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Respect runtime environment variables (e.g. from docker-compose exec -e)
            $existingValue = getenv($name);
            if ($existingValue !== false) {
                $value = $existingValue;
            }
            
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            
            // Also define as constants for legacy code
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }
}

// Load test environment
loadEnv();

// Setup cache directory for tests
if (!defined('FS_CACHE')) {
    define('FS_CACHE', __DIR__ . '/cache/');
}

if (!is_dir(FS_CACHE)) {
    mkdir(FS_CACHE, 0755, true);
}
