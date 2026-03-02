<?php
use Simpl\Session;
use Simpl\DB;

beforeEach(function () {
    // Mock the DB dependency
    $this->db = Mockery::mock(DB::class);
    // getDatabase is called in constructor
    $this->db->shouldReceive('getDatabase')->andReturn('test_db');
});

afterEach(function () {
    Mockery::close();
});

it('initializes correctly', function () {
    $session = new Session($this->db);
    expect($session)->toBeInstanceOf(Session::class);
});

it('reads existing session data', function () {
    $sessionId = 'test_session_id';
    $sessionData = ['user_id' => 123, 'role' => 'admin'];
    
    // Mock Query behavior
    $this->db->shouldReceive('Query')->withArgs(function ($sql) use ($sessionId) {
        return strpos($sql, "SELECT * FROM `session` WHERE ses_id = '$sessionId'") !== false;
    }, 'test_db', false)->once()->andReturn('mock_resource');
    
    // Mock NumRows behavior
    $this->db->shouldReceive('NumRows')->with('mock_resource')->once()->andReturn(1);
    
    // Mock FetchArray behavior
    $this->db->shouldReceive('FetchArray')->with('mock_resource')->once()->andReturn([
        'ses_value' => serialize($sessionData),
        'ses_start' => time()
    ]);
    
    $session = new Session($this->db);
    $result = $session->read($sessionId);
    
    expect($result)->toBe($sessionData);
});

it('returns empty string when session not found (read)', function () {
    $sessionId = 'nonexistent_id';
    
    // Mock Query returning empty or mock resource
    $this->db->shouldReceive('Query')->once()->andReturn('mock_resource');
    
    // Mock NumRows returning 0
    $this->db->shouldReceive('NumRows')->with('mock_resource')->once()->andReturn(0);
    
    $session = new Session($this->db);
    $result = $session->read($sessionId);
    
    expect($result)->toBe('');
});

it('writes new session data (insert)', function () {
    $sessionId = 'new_session_id';
    $data = ['foo' => 'bar'];
    
    // Mock Start Time setting (read then write flow usually)
    // Write checks for existence first
    $this->db->shouldReceive('Query')->once()->andReturn('mock_check_resource');
    $this->db->shouldReceive('NumRows')->with('mock_check_resource')->once()->andReturn(0);
    
    // Expect Perform (Insert)
    $this->db->shouldReceive('Perform')->withArgs(function ($table, $info, $type) use ($sessionId) {
        return $table === 'session' && 
               $type === 'insert' &&
               $info['ses_id'] === $sessionId;
    })->once()->andReturn(true);
    
    $session = new Session($this->db);
    $result = $session->write($sessionId, $data);
    
    expect($result)->toBeTrue();
});

it('updates existing session data', function () {
    $sessionId = 'existing_session_id';
    $data = ['foo' => 'updated'];
    
    // Write checks for existence first
    $this->db->shouldReceive('Query')->once()->andReturn('mock_check_resource');
    $this->db->shouldReceive('NumRows')->with('mock_check_resource')->once()->andReturn(1); // Exists
    
    // Expect Perform (Update)
    $this->db->shouldReceive('Perform')->withArgs(function ($table, $info, $type) {
        return $table === 'session' && 
               $type === 'update';
    })->once()->andReturn(true);
    
    $session = new Session($this->db);
    $result = $session->write($sessionId, $data);
    
    expect($result)->toBeTrue();
});

it('destroys a session', function () {
    $sessionId = 'session_to_kill';
    
    $this->db->shouldReceive('Query')->withArgs(function ($sql) use ($sessionId) {
        return strpos($sql, "DELETE FROM `session` WHERE `ses_id` = '$sessionId'") !== false;
    }, 'test_db', false)->once()->andReturn(true);
    
    $session = new Session($this->db);
    $result = $session->destroy($sessionId);
    
    expect($result)->toBeTrue();
});

it('performs garbage collection', function () {
    // We expect GC logic because DB_SESSIONS_GC should be true or false
    // But let's assume if it runs Query, GC is active.
    // Wait, env puts GC=false, so it returns gc_defer() which returns true.
    // If we want to test GC SQL, we need to constant to be true OR mock the class to override logic?
    // Constants are global, so defined in loadEnv.
    
    if (constant('DB_SESSIONS_GC') === false) {
        $session = new Session($this->db);
        expect($session->gc())->toBeTrue();
        return;
    }

    // If GC was true...
    $this->db->shouldReceive('Query')->withArgs(function ($sql) {
        return strpos($sql, 'DELETE FROM `session` WHERE `last_access` <') !== false;
    }, 'test_db', false)->once()->andReturn(true);
    
    $session = new Session($this->db);
    expect($session->gc())->toBeTrue();
});
