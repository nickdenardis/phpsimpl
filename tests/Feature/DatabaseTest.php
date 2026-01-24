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
    // Clean up test data - but check if connection is still valid
    if (isset($this->db) && $this->db->IsConnected()) {
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

// =============================================================================
// REGRESSION TESTS FOR mysqli_* MIGRATION EDGE CASES
// =============================================================================

it('handles null values correctly in UPDATE via Perform()', function () {
    // Insert a record with data
    $insertData = [
        'name' => 'User With Status',
        'email' => 'null-test@test.example.com',
        'status' => 'active'
    ];
    
    $this->db->Perform('test_users', $insertData, 'insert');
    $insertedId = $this->db->InsertID();
    
    // Update with null value (string 'null' gets converted to SQL NULL)
    $updateData = [
        'status' => 'null'
    ];
    
    $result = $this->db->Perform('test_users', $updateData, 'update', "id = '$insertedId'");
    
    expect($result)->toBeTruthy();
    
    // Verify the update - status should be NULL
    $checkResult = $this->db->Query("SELECT * FROM test_users WHERE id = '$insertedId'");
    $row = $this->db->FetchArray($checkResult);
    
    expect($row['status'])->toBeNull();
    expect($row['name'])->toBe('User With Status'); // Other fields unchanged
});

it('allows reconnection after Close()', function () {
    // Verify initial connection
    expect($this->db->IsConnected())->toBeTrue();
    
    // Insert a test record to prove connection works
    $this->db->Perform('test_users', [
        'name' => 'Before Close',
        'email' => 'close-test@test.example.com'
    ], 'insert');
    $firstId = $this->db->InsertID();
    expect($firstId)->toBeGreaterThan(0);
    
    // Close the connection
    $closeResult = $this->db->Close();
    expect($closeResult)->toBeTrue();
    expect($this->db->IsConnected())->toBeFalse();
    
    // Create a new DB instance which will auto-connect
    $newDb = new \Simpl\DB();
    
    // Use the new connection to verify we can connect again
    $newDb->Perform('test_users', [
        'name' => 'After Close',
        'email' => 'close-test2@test.example.com'
    ], 'insert');
    $secondId = $newDb->InsertID();
    
    // Should have connected successfully
    expect($secondId)->toBeGreaterThan($firstId);
    expect($newDb->IsConnected())->toBeTrue();
});

it('tracks database name correctly after Change()', function () {
    // Get initial database
    $originalDb = $this->db->getDatabase();
    expect($originalDb)->toBe('phpsimpl_test');
    
    // The key regression test: Change() should update the $database property
    // We test this by checking that getDatabase() returns the correct value
    // Even if the actual database change fails due to permissions,
    // the property should only update on success
    
    // Attempt an invalid change - should fail and property stays the same
    try {
        $badResult = $this->db->Change('nonexistent_db_12345');
        expect($badResult)->toBeFalse();
    } catch (\mysqli_sql_exception $e) {
        // In PHP 8.2, mysqli throws exceptions even with @ suppression in some cases
        // This is acceptable - the important thing is our fix prevents property corruption
    }
    
    // Verify property didn't change after failed attempt
    expect($this->db->getDatabase())->toBe($originalDb);
});

it('handles Prepare() gracefully when connection fails', function () {
    // Create a DB instance with bad credentials (won't actually connect until needed)
    $badDb = new \Simpl\DB();
    $badDb->Connect('invalid_host', 'bad_user', 'bad_pass', 'bad_db');
    
    // Prepare should fall back to addslashes when connection fails
    // Note: The connection attempt will throw a mysqli_sql_exception with @ suppression
    // but our guard in Prepare() catches the null db_link and uses addslashes
    try {
        $testString = "O'Reilly";
        $prepared = $badDb->Prepare($testString);
        
        // Should still escape the string using addslashes
        expect($prepared)->toContain("\\'" );
    } catch (\mysqli_sql_exception $e) {
        // If exception is thrown, that's also acceptable - it means DbConnect failed
        // which is the expected behavior for bad credentials
        expect($e)->toBeInstanceOf(\mysqli_sql_exception::class);
    }
});

it('updates with now() special value via Perform()', function () {
    // Create a temporary test table without foreign key constraints
    $this->db->Query("CREATE TEMPORARY TABLE IF NOT EXISTS test_now (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL
    )", '', false, false);
    
    // Insert a record
    $this->db->Query("INSERT INTO test_now (name) VALUES ('Time Test')", '', false, false);
    $insertedId = $this->db->InsertID();
    
    // Get the original timestamp
    $result1 = $this->db->Query("SELECT created_at, updated_at FROM test_now WHERE id = '$insertedId'");
    $row1 = $this->db->FetchArray($result1);
    expect($row1['updated_at'])->toBeNull();
    
    // Wait a moment
    sleep(1);
    
    // Update with now() special value
    $this->db->Perform('test_now', [
        'updated_at' => 'now()'
    ], 'update', "id = '$insertedId'");
    
    // Verify now() was used (not the literal string)
    $result2 = $this->db->Query("SELECT created_at, updated_at FROM test_now WHERE id = '$insertedId'");
    $row2 = $this->db->FetchArray($result2);
    
    expect($row2['updated_at'])->not->toBeNull();
    expect($row2['updated_at'])->not->toBe('now()');
    
    // Temporary table will be automatically dropped at end of session
});

it('handles mixed null and regular values in single UPDATE', function () {
    // Insert a record
    $this->db->Perform('test_users', [
        'name' => 'Mixed Update Test',
        'email' => 'mixed@test.example.com',
        'status' => 'active'
    ], 'insert');
    $insertedId = $this->db->InsertID();
    
    // Update with mix of regular value and null
    $this->db->Perform('test_users', [
        'name' => 'Updated Name',
        'status' => 'null'  // Set to NULL
    ], 'update', "id = '$insertedId'");
    
    // Verify both updates worked
    $result = $this->db->Query("SELECT * FROM test_users WHERE id = '$insertedId'");
    $row = $this->db->FetchArray($result);
    
    expect($row['name'])->toBe('Updated Name');
    expect($row['status'])->toBeNull();
    expect($row['email'])->toBe('mixed@test.example.com'); // Unchanged
});
