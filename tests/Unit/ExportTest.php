<?php

use Simpl\Export;

it('exports a null column value to CSV without a deprecation notice', function () {
    $export = new Export;
    $export->SetDisplay(['username', 'bio']);
    $export->SetData([['username' => 'jdoe', 'bio' => null]]);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $csv = $export->Retrieve('csv');

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($csv)->toContain('jdoe');
});

it('exports a null column value to XML without a deprecation notice', function () {
    $export = new Export;
    $export->SetDisplay(['username', 'bio']);
    $export->SetData([['username' => 'jdoe', 'bio' => null]]);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $xml = $export->Retrieve('xml');

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($xml)->toContain('<username>jdoe</username>');
});

it('exports a null column value to SQL without a deprecation notice', function () {
    $export = new Export;
    $export->SetDisplay(['username', 'bio']);
    $export->SetData([['username' => 'jdoe', 'bio' => null]]);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $sql = $export->Retrieve('sql');

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($sql)->toContain('jdoe');
});
