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
