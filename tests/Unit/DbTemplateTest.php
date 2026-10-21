<?php

use Simpl\DbTemplate;

beforeEach(function () {
    $this->reflection = new ReflectionClass(DbTemplate::class);
    $this->template = $this->reflection->newInstanceWithoutConstructor();
    $this->validTypeMethod = $this->reflection->getMethod('ValidType');
    $this->validTypeMethod->setAccessible(true);
});

it('identifies unsigned fields using PHP 8.2 mysqli flags', function () {
    $field = (object) [
        'name'  => 'id',
        'type'  => MYSQLI_TYPE_LONG,
        'flags' => MYSQLI_UNSIGNED_FLAG | MYSQLI_NUM_FLAG,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('unsigned');
});

it('identifies unsigned fields using legacy property', function () {
    $field = (object) [
        'name'     => 'id',
        'type'     => 3,
        'unsigned' => 1,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('unsigned');
});

it('identifies float/double/decimal fields using PHP 8.2 type constants', function ($typeConstant) {
    $field = (object) [
        'name'  => 'amount',
        'type'  => $typeConstant,
        'flags' => MYSQLI_NUM_FLAG,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('float');
})->with([
    'MYSQLI_TYPE_FLOAT'      => [MYSQLI_TYPE_FLOAT],
    'MYSQLI_TYPE_DOUBLE'     => [MYSQLI_TYPE_DOUBLE],
    'MYSQLI_TYPE_DECIMAL'    => [MYSQLI_TYPE_DECIMAL],
    'MYSQLI_TYPE_NEWDECIMAL' => [MYSQLI_TYPE_NEWDECIMAL],
]);

it('identifies real/float fields using legacy string type', function () {
    $field = (object) [
        'name' => 'amount',
        'type' => 'real',
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('float');
});

it('identifies integer fields using PHP 8.2 numeric flag', function () {
    $field = (object) [
        'name'  => 'count',
        'type'  => MYSQLI_TYPE_LONG,
        'flags' => MYSQLI_NUM_FLAG,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('int');
});

it('identifies integer fields using legacy numeric property', function () {
    $field = (object) [
        'name'    => 'count',
        'numeric' => 1,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('int');
});

it('identifies email field by column name', function () {
    $field = (object) [
        'name'  => 'email',
        'type'  => MYSQLI_TYPE_VAR_STRING,
        'flags' => 0,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('email');
});

it('returns NULL for standard non-numeric string fields', function () {
    $field = (object) [
        'name'  => 'username',
        'type'  => MYSQLI_TYPE_VAR_STRING,
        'flags' => 0,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBeNull();
});

it('prioritizes unsigned over int validation rule', function () {
    // Unsigned fields set both MYSQLI_UNSIGNED_FLAG and MYSQLI_NUM_FLAG
    $field = (object) [
        'name'  => 'id',
        'type'  => MYSQLI_TYPE_LONG,
        'flags' => MYSQLI_UNSIGNED_FLAG | MYSQLI_NUM_FLAG,
    ];

    $result = $this->validTypeMethod->invokeArgs($this->template, [&$field]);
    expect($result)->toBe('unsigned');
});

it('exports a row with a null column value without a deprecation notice', function () {
    $resultsProperty = $this->reflection->getProperty('results');
    $resultsProperty->setAccessible(true);
    $resultsProperty->setValue($this->template, [
        ['username' => 'jdoe', 'bio' => null],
    ]);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $xml = $this->template->Export('xml', ['username', 'bio']);

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($xml)->toContain('<username>jdoe</username>');
});

it('handles non-object inputs gracefully in ValidType()', function ($input) {
    $result = $this->validTypeMethod->invokeArgs($this->template, [&$input]);
    expect($result)->toBeNull();
})->with([
    'null'     => [null],
    'boolean'  => [false],
    'string'   => ['not_an_object'],
    'array'    => [['name' => 'email']],
]);

it('executes Search() with joined tables and non-returned search fields without warnings', function () {
    $fakeDb = new class {
        public array $queries = [];
        private array $rows = [
            ['id' => 1, 'title' => 'Campus Map', 'description' => null],
        ];
        private int $idx = 0;

        public function Query($query, $database = '')
        {
            $this->queries[] = $query;
            $this->idx = 0;
            return true;
        }

        public function FetchArray($result)
        {
            if ($this->idx < count($this->rows)) {
                return $this->rows[$this->idx++];
            }
            return false;
        }
    };

    $main = $this->reflection->newInstanceWithoutConstructor();
    $main->table = 'locations';
    $main->database = 'maps_db';
    $main->primary = 'id';

    $joined = $this->reflection->newInstanceWithoutConstructor();
    $joined->table = 'categories';
    $joined->database = 'maps_db';
    $joined->primary = 'cat_id';

    $dbLinkProp = $this->reflection->getProperty('db_link');
    $dbLinkProp->setAccessible(true);
    $dbLinkProp->setValue($main, $fakeDb);

    $main->Join($joined, 'cat_id', 'LEFT');

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    // 'notes' is in $search_fields but not in $return_fields; 'description' is NULL in row
    $count = $main->Search('Campus', ['title', 'description', 'notes'], ['title']);

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($count)->toBe(1);
    expect($fakeDb->queries[0])->toContain('LEFT JOIN `maps_db`.`categories`');
});

it('renders DisplayList() with unmapped int_options and options without undefined array key warnings', function () {
    if (!defined('SIMPL_TABLE_STRIPES')) {
        define('SIMPL_TABLE_STRIPES', true);
    }
    $_SERVER['PHP_SELF'] = '/index.php';
    $_SESSION = [];

    $fieldObj = new \Simpl\Field(new \Simpl\Validate());
    $fieldObj->Set('name', 'status');
    $fieldObj->Set('label', 'Status');
    $fieldObj->Set('options', ['1' => 'Published']);

    $fieldsProp = $this->reflection->getProperty('fields');
    $fieldsProp->setAccessible(true);
    $fieldsProp->setValue($this->template, ['status' => $fieldObj]);

    $this->template->table = 'locations';
    $this->template->primary = 'id';
    $this->template->results = [
        1 => ['id' => 1, 'status' => 'unmapped_value'],
    ];

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    ob_start();
    $this->template->DisplayList(
        ['status', 'virtual_column'],
        ['status' => '<a href="?id={$item_id}">{$data}</a>'],
        ['virtual_column' => ['only_mapped' => 'Yes']],
        false
    );
    $output = ob_get_clean();

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($output)->toContain('table_locations');
});

