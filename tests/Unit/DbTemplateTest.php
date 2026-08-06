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

it('handles non-object inputs gracefully in ValidType()', function ($input) {
    $result = $this->validTypeMethod->invokeArgs($this->template, [&$input]);
    expect($result)->toBeNull();
})->with([
    'null'     => [null],
    'boolean'  => [false],
    'string'   => ['not_an_object'],
    'array'    => [['name' => 'email']],
]);
