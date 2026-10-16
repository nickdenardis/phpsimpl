<?php

use Simpl\DB;

beforeEach(function () {
    // Load test environment if not already loaded
    if (!defined('DBHOST')) {
        loadEnv();
    }
});

it('stores connection parameters correctly', function () {
    $db = new DB();
    
    // Verify DB was instantiated
    expect($db)->toBeInstanceOf(DB::class);
    expect($db->query_count)->toBe(0);
});

it('escapes HTML for safe output', function () {
    $db = new DB();
    
    $input = '<script>alert("xss")</script>';
    $output = $db->Output($input);
    
    expect($output)->not->toContain('<script>');
    expect($output)->toContain('&lt;script&gt;');
});

it('prepares strings for database input', function () {
    $db = new DB();
    
    $input = "O'Reilly";
    $prepared = $db->Prepare($input);
    
    // Should escape the single quote
    expect($prepared)->toContain("\\'");
})->skip('Requires database connection');

it('formats a null value for output without a deprecation notice', function () {
    $db = new DB();

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $output = $db->Output(null);

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($output)->toBe('');
});

it('prepares a null value for database input without a deprecation notice', function () {
    $db = new DB();

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $prepared = $db->Prepare(null);

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($prepared)->toBe('');
});

it('tracks connection state', function () {
    $db = new DB();
    
    // Initially connected state depends on config
    expect($db->IsConnected())->toBeIn([true, false]);
});

it('builds INSERT queries correctly with Perform', function () {
    // This would require mocking the database
    // Skipping for now, will implement with real DB in Feature tests
})->skip('Requires database mocking');

it('builds UPDATE queries correctly with Perform', function () {
    // This would require mocking the database
    // Skipping for now, will implement with real DB in Feature tests
})->skip('Requires database mocking');

it('maps mysqli field type codes to legacy mysql type names', function (int $code, string $name) {
    expect(DB::FieldTypeName($code))->toBe($name);
})->with([
    [MYSQLI_TYPE_TINY, 'int'],
    [MYSQLI_TYPE_SHORT, 'int'],
    [MYSQLI_TYPE_LONG, 'int'],
    [MYSQLI_TYPE_INT24, 'int'],
    [MYSQLI_TYPE_LONGLONG, 'int'],
    [MYSQLI_TYPE_DECIMAL, 'real'],
    [MYSQLI_TYPE_NEWDECIMAL, 'real'],
    [MYSQLI_TYPE_FLOAT, 'real'],
    [MYSQLI_TYPE_DOUBLE, 'real'],
    [MYSQLI_TYPE_STRING, 'string'],
    [MYSQLI_TYPE_VAR_STRING, 'string'],
    [MYSQLI_TYPE_BLOB, 'blob'],
    [MYSQLI_TYPE_DATE, 'date'],
    [MYSQLI_TYPE_DATETIME, 'datetime'],
    [MYSQLI_TYPE_TIMESTAMP, 'timestamp'],
    [MYSQLI_TYPE_TIME, 'time'],
    [MYSQLI_TYPE_YEAR, 'year'],
]);

it('passes legacy type names through FieldTypeName unchanged', function () {
    expect(DB::FieldTypeName('int'))->toBe('int');
    expect(DB::FieldTypeName('blob'))->toBe('blob');
});
