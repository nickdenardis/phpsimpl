<?php
/**
 * Regression coverage for Field::Form() reading settings from the
 * global Simpl instance instead of a nonexistent $this->settings.
 */

use Simpl\Field;
use Simpl\Simpl;
use Simpl\Validate;

it('renders a form field without warnings using the global Simpl settings', function () {
    global $mySimpl;
    $mySimpl = new Simpl();

    $field = new Field(new Validate());
    $field->Set('name', 'test_field');
    $field->Set('label', 'Test Field');
    $field->Set('type', 'text');
    $field->Set('value', 'preset value');
    $field->Set('required', true);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    ob_start();
    $field->Form();
    $output = ob_get_clean();

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($output)->toContain('Test Field');
});

it('honors a custom required_indicator setting from the global Simpl instance', function () {
    global $mySimpl;
    $mySimpl = new Simpl();
    $mySimpl->settings['form']['required_indicator'] = 'after';

    $field = new Field(new Validate());
    $field->Set('name', 'test_field');
    $field->Set('label', 'Test Field');
    $field->Set('type', 'text');
    $field->Set('value', 'preset value');
    $field->Set('required', true);

    ob_start();
    $field->Form();
    $output = ob_get_clean();

    $labelPos = strpos($output, '</label>');
    $starPos = strpos($output, '<em>*</em>');

    expect($starPos)->not->toBeFalse();
    expect($starPos)->toBeLessThan($labelPos);
});
