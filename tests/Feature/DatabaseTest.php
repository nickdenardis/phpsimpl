<?php

use Simpl\DB;

beforeEach(function () {
    // Ensure database is connected
    if (!defined('DBHOST')) {
        loadEnv();
    }
    
    $this->db = new DB();
    
    // Create a test table if it doesn't exist
    $createTable = "CREATE TABLE IF NOT EXISTS test_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $this->db->Query($createTable, '', false, false);
    
    // Clean up any existing test data
    $this->db->Query("DELETE FROM test_users WHERE email LIKE '%@test.example.com'", '', false, false);
});

afterEach(function () {
    // Clean up test data
    if (isset($this->db)) {
        $this->db->Query("DELETE FROM test_users WHERE email LIKE '%@test.example.com'", '', false, false);
    }
});

it('connects to test database', function () {
    expect($this->db)->toBeInstanceOf(DB::class);
    expect($this->db->IsConnected())->toBeTrue();
});

it('inserts records via Perform()', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'test1@test.example.com',
        'status' => 'active'
    ];
    
    $result = $this->db->Perform('test_users', $data, 'insert');
    
    expect($result)->toBeTruthy();
    expect($this->db->InsertID())->toBeGreaterThan(0);
    
    // Verify the record was inserted
    $checkResult = $this->db->Query("SELECT * FROM test_users WHERE email = 'test1@test.example.com'");
    expect($this->db->NumRows($checkResult))->toBe(1);
    
    $row = $this->db->FetchArray($checkResult);
    expect($row['name'])->toBe('Test User');
    expect($row['email'])->toBe('test1@test.example.com');
});

it('updates records via Perform()', function () {
    // First insert a record
    $insertData = [
        'name' => 'Original Name',
        'email' => 'update-test@test.example.com',
        'status' => 'active'
    ];
    
    $this->db->Perform('test_users', $insertData, 'insert');
    $insertedId = $this->db->InsertID();
    
    // Now update it
    $updateData = [
        'name' => 'Updated Name',
        'status' => 'inactive'
    ];
    
    $result = $this->db->Perform('test_users', $updateData, 'update', "id = '$insertedId'");
    
    expect($result)->toBeTruthy();
    
    // Verify the update
    $checkResult = $this->db->Query("SELECT * FROM test_users WHERE id = '$insertedId'");
    $row = $this->db->FetchArray($checkResult);
    
    expect($row['name'])->toBe('Updated Name');
    expect($row['status'])->toBe('inactive');
    expect($row['email'])->toBe('update-test@test.example.com'); // Should remain unchanged
});

it('executes SELECT queries', function () {
    // Insert test data
    $this->db->Perform('test_users', [
        'name' => 'User 1',
        'email' => 'user1@test.example.com'
    ], 'insert');
    
    $this->db->Perform('test_users', [
        'name' => 'User 2',
        'email' => 'user2@test.example.com'
    ], 'insert');
    
    // Query for them
    $result = $this->db->Query("SELECT * FROM test_users WHERE email LIKE '%@test.example.com' ORDER BY id");
    
    expect($this->db->NumRows($result))->toBeGreaterThanOrEqual(2);
    
    $count = 0;
    while ($row = $this->db->FetchArray($result)) {
        expect($row)->toHaveKey('id');
        expect($row)->toHaveKey('name');
        expect($row)->toHaveKey('email');
        $count++;
    }
    
    expect($count)->toBeGreaterThanOrEqual(2);
});

it('executes DELETE queries', function () {
    // Insert a record
    $this->db->Perform('test_users', [
        'name' => 'Delete Me',
        'email' => 'delete@test.example.com'
    ], 'insert');
    
    $insertedId = $this->db->InsertID();
    
    // Delete it
    $this->db->Query("DELETE FROM test_users WHERE id = '$insertedId'");
    
    // Verify it's gone
    $checkResult = $this->db->Query("SELECT * FROM test_users WHERE id = '$insertedId'");
    expect($this->db->NumRows($checkResult))->toBe(0);
});

it('handles query failures gracefully', function () {
    // This will cause an error and call the Error() method which uses die()
    // We can't test this without mocking, so skip for now
})->skip('Error method uses die() which halts execution');

it('properly escapes strings in Prepare()', function () {
    $dangerous = "'; DROP TABLE test_users; --";
    $escaped = $this->db->Prepare($dangerous);
    
    // Should have escaped the dangerous characters
    expect($escaped)->not->toBe($dangerous);
    expect($escaped)->toContain("\\'");
});
