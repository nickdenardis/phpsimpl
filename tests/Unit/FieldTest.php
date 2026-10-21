<?php
/**
 * Regression coverage for Field.
 */

use Simpl\Field;
use Simpl\Simpl;
use Simpl\Upload;
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

it('renders a field with no label or value without a deprecation notice', function () {
    global $mySimpl;
    $mySimpl = new Simpl();

    $field = new Field(new Validate());
    $field->Set('name', 'test_field');
    $field->Set('type', 'text');

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    ob_start();
    $field->Form();
    ob_get_clean();

    restore_error_handler();

    expect($errors)->toBe([]);
});

it('renders a Simpl\Upload field as a file input when empty and hidden/remove input when populated', function () {
    global $mySimpl;
    $mySimpl = new Simpl();

    $upload = (new ReflectionClass(Upload::class))->newInstanceWithoutConstructor();

    $field = new Field(new Validate());
    $field->Set('name', 'photo');
    $field->Set('label', 'Photo');
    $field->Set('options', $upload);

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    ob_start();
    $field->Form();
    $emptyOutput = ob_get_clean();

    $field->Set('value', 'avatar.jpg');
    ob_start();
    $field->Form();
    $populatedOutput = ob_get_clean();

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($emptyOutput)->toContain('type="file"');
    expect($populatedOutput)->toContain('avatar.jpg');
    expect($populatedOutput)->toContain('Remove File');
});

it('renders radio and checkbox fields without undefined variable warnings', function () {
    global $mySimpl;
    $mySimpl = new Simpl();

    $errors = [];
    set_error_handler(function ($no, $str) use (&$errors) {
        $errors[] = $str;
        return true;
    });

    $radio = new Field(new Validate());
    $radio->Set('name', 'status');
    $radio->Set('options', ['active' => 'Active', 'inactive' => 'Inactive']);
    $radio->Set('config', 'radio');
    $radio->Set('value', 'active');

    ob_start();
    $radio->Form();
    $radioOutput = ob_get_clean();

    $checkbox = new Field(new Validate());
    $checkbox->Set('name', 'tags');
    $checkbox->Set('options', ['news' => 'News', 'events' => 'Events']);
    $checkbox->Set('config', 'checkbox');
    $checkbox->Set('value', null);

    ob_start();
    $checkbox->Form();
    $checkboxOutput = ob_get_clean();

    restore_error_handler();

    expect($errors)->toBe([]);
    expect($radioOutput)->toContain('type="radio"');
    expect($radioOutput)->toContain('checked="checked"');
    expect($checkboxOutput)->toContain('type="checkbox"');
});

