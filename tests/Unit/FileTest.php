<?php
use Simpl\File;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir() . '/simpl_test_' . uniqid();
    if (!is_dir($this->tempDir)) {
        mkdir($this->tempDir);
    }
    $this->filePath = $this->tempDir . '/test.txt';
    file_put_contents($this->filePath, 'Hello World');
});

afterEach(function () {
    if (file_exists($this->filePath)) unlink($this->filePath);
    if (is_dir($this->tempDir)) rmdir($this->tempDir);
});

it('initializes file object correctly', function () {
    $file = new File('test.txt', $this->tempDir);
    expect($file->filename)->toBe('test.txt');
});

it('checks existence', function () {
    $file = new File('test.txt', $this->tempDir);
    expect($file->Exists())->toBeTrue();
    
    $file2 = new File('nonexistent.txt', $this->tempDir);
    expect($file2->Exists())->toBeFalse();
});

it('gets contents', function () {
    $file = new File('test.txt', $this->tempDir);
    expect($file->GetContents())->toBe('Hello World');
});

it('renames file', function () {
    $file = new File('test.txt', $this->tempDir);

    $result = $file->Rename('renamed.txt');
    
    expect($result)->toBeTrue();
    expect(file_exists($this->tempDir . '/renamed.txt'))->toBeTrue();
    expect(file_exists($this->tempDir . '/test.txt'))->toBeFalse();
    expect($file->filename)->toBe('renamed.txt');
});

it('formats filename safely', function () {
    $file = new File('bad @ file name!.txt', $this->tempDir);
    $file->FormatFilename();
    expect($file->filename)->toMatch('/^[A-Za-z0-9._-]+$/');
});

it('deletes file', function () {
    $file = new File('test.txt', $this->tempDir);
    expect($file->Delete())->toBeTrue();
    expect(file_exists($this->tempDir . '/test.txt'))->toBeFalse();
});
