<?php
/**
 * Regression coverage: DbTemplate's WHERE-clause builders now escape
 * GetPrimary()/GetValue() via Prepare() instead of concatenating raw.
 */

use Simpl\DB;
use Simpl\DbTemplate;

beforeEach(function () {
    if (!defined('DBHOST')) {
        loadEnv();
    }

    $this->db = new DB();

    global $db;
    $db = $this->db;

    $this->db->Query("CREATE TABLE IF NOT EXISTS test_injection_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        status VARCHAR(50) DEFAULT 'active'
    )", '', false, false);

    $this->db->Query("DELETE FROM test_injection_users", '', false, false);

    $this->db->Perform('test_injection_users', ['email' => 'victim1@test.example.com', 'status' => 'active'], 'insert');
    $this->victimId1 = $this->db->InsertID();

    $this->db->Perform('test_injection_users', ['email' => 'victim2@test.example.com', 'status' => 'active'], 'insert');
    $this->victimId2 = $this->db->InsertID();
});

afterEach(function () {
    if (isset($this->db) && $this->db->IsConnected()) {
        $this->db->Query("DELETE FROM test_injection_users", '', false, false);
    }
});

it('does not let a crafted primary key bypass GetInfo() to another row', function () {
    $template = new DbTemplate('test_injection_users', $this->db);

    // 999 doesn't exist, so a match here would mean the quotes broke out.
    $template->SetPrimary("999' OR '1'='1");

    $found = $template->GetInfo();

    expect($found)->toBeFalse();
});

it('does not let a crafted condition value bypass Delete() into other rows', function () {
    $template = new DbTemplate('test_injection_users', $this->db);

    // Would delete every row if concatenated unescaped into the WHERE clause.
    $template->SetValue('email', "nobody@test.example.com' OR '1'='1");
    $template->Delete([], ['email']);

    $remaining = $this->db->Query('SELECT COUNT(*) as c FROM test_injection_users');
    $row = $this->db->FetchArray($remaining);

    expect((int) $row['c'])->toBe(2);
});

it('does not let a crafted condition value bypass UpdateValue() into other rows', function () {
    $template = new DbTemplate('test_injection_users', $this->db);

    // Would update every row's status if concatenated unescaped into the WHERE clause.
    $template->SetValue('email', "nobody@test.example.com' OR '1'='1");
    $template->UpdateValue('status', 'compromised', ['email']);

    $result = $this->db->Query("SELECT COUNT(*) as c FROM test_injection_users WHERE status = 'compromised'");
    $row = $this->db->FetchArray($result);

    expect((int) $row['c'])->toBe(0);
});
