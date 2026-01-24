<?php

it('escapes HTML entities with h()', function () {
    $input = '<script>alert("xss")</script>';
    $output = h($input);
    
    expect($output)->not->toContain('<script>');
    expect($output)->toContain('&lt;script&gt;');
});

it('escapes entities with e()', function () {
    $input = '<div class="test">Hello & goodbye</div>';
    $output = e($input);
    
    expect($output)->not->toContain('<div');
    expect($output)->toContain('&lt;div');
    expect($output)->toContain('&amp;');
});

it('safely accesses array values with a()', function () {
    $array = ['key' => 'value', 'number' => 42];
    
    expect(a($array, 'key'))->toBe('value');
    expect(a($array, 'number'))->toBe(42);
    expect(a($array, 'nonexistent'))->toBe(false);
});

it('handles DateTimeDiff for recent times', function () {
    $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $result = DateTimeDiff($oneHourAgo);
    
    expect($result)->toBeString();
    expect($result)->toContain('hour');
});

it('processes search terms correctly', function () {
    $terms = 'hello world "exact phrase"';
    $result = search_split_terms($terms);
    
    expect($result)->toBeArray();
    expect($result)->toContain('hello');
    expect($result)->toContain('world');
    expect($result)->toContain('exact phrase');
});

it('escapes special characters for RLIKE', function () {
    $input = 'test.string';
    $escaped = search_escape_rlike($input);
    
    // Period should be escaped
    expect($escaped)->toContain('\\.');
});

it('transforms search terms', function () {
    $term = 'test';
    $transformed = search_transform_term($term);
    
    expect($transformed)->toBeString();
    expect($transformed)->toContain('test');
});
