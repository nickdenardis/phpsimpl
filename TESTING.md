# Testing Guide for PHPSimpl

This guide explains how to run and write tests for the PHPSimpl framework using PEST.

## Table of Contents

- [Quick Start](#quick-start)
- [Running Tests](#running-tests)
- [Test Structure](#test-structure)
- [Writing Tests](#writing-tests)
- [Test Application](#test-application)
- [Continuous Integration](#continuous-integration)

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Start Test Database

```bash
docker-compose up -d
```

Wait ~10 seconds for MariaDB to initialize.

### 3. Initialize Database

```bash
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql
```

### 4. Run Tests

```bash
composer test
```

## Running Tests

### Run All Tests

```bash
./vendor/bin/pest
```

Or use the composer script:

```bash
composer test
```

### Run Specific Test Suites

**Unit tests only:**
```bash
./vendor/bin/pest tests/Unit
```

**Feature tests only:**
```bash
./vendor/bin/pest tests/Feature
```

### Run Specific Test Files

```bash
./vendor/bin/pest tests/Unit/DBTest.php
./vendor/bin/pest tests/Unit/FormTest.php
```

### Run with Coverage

```bash
composer test:coverage
```

### Filter Tests by Name

```bash
./vendor/bin/pest --filter="validates emails"
```

## Test Structure

```
tests/
├── Unit/              # Unit tests (no database required)
│   ├── DBTest.php
│   ├── FormTest.php
│   ├── ValidateTest.php
│   └── FunctionsTest.php
├── Feature/           # Integration tests (requires database)
│   └── DatabaseTest.php
Pest.php               # PEST configuration
```

## Writing Tests

### Unit Test Example

```php
<?php

use Simpl\Validate;

beforeEach(function () {
    $this->validate = new Validate();
});

it('validates emails correctly')->with([
    ['test@example.com', true],
    ['invalid-email', false],
])->run(function ($email, $shouldBeValid) {
    $result = $this->validate->Check('email', $email);
    
    if ($shouldBeValid) {
        expect($result)->toBeTrue();
    } else {
        expect($result)->toBeFalse();
    }
});
```

### Feature Test Example

```php
<?php

use Simpl\DB;

beforeEach(function () {
    $this->db = new DB();
    // Setup test data
});

afterEach(function () {
    // Cleanup test data
});

it('inserts records via Perform()', function () {
    $data = [
        'name' => 'Test User',
        'email' => 'test@example.com'
    ];
    
    $result = $this->db->Perform('test_users', $data, 'insert');
    
    expect($result)->toBeTruthy();
    expect($this->db->InsertID())->toBeGreaterThan(0);
});
```

### Using Datasets

```php
it('validates different inputs')->with([
    ['valid input', true],
    ['invalid input', false],
    ['edge case', false],
])->run(function ($input, $expected) {
    $result = someFunction($input);
    expect($result)->toBe($expected);
});
```

## Test Application

A working test application is provided in `/test-app` to manually verify functionality.

### Start the Test App

```bash
cd test-app
php -S localhost:8000
```

Visit: **http://localhost:8000**

### What It Tests

- ✅ Database connectivity
- ✅ Query execution
- ✅ Form handling and validation
- ✅ Data sanitization
- ✅ HTML escaping

See [test-app/README.md](test-app/README.md) for more details.

## Database Management

### Access MariaDB CLI

```bash
docker exec -it phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test
```

### View Logs

```bash
docker-compose logs mariadb
```

### Stop Database

```bash
docker-compose down
```

### Reset Database

```bash
docker-compose down -v  # Removes volumes
docker-compose up -d
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql
```

## Best Practices

### 1. Test Current Behavior First

Before making any changes:
- ✅ Run all tests to establish baseline
- ✅ Run test application and document behavior
- ✅ Take screenshots if testing UI changes

### 2. Write Tests for Bug Fixes

When fixing a bug:
1. Write a test that reproduces the bug
2. Verify test fails
3. Fix the bug
4. Verify test passes

### 3. Maintain Test Isolation

- Each test should be independent
- Use `beforeEach`/`afterEach` for setup/cleanup
- Don't rely on test execution order

### 4. Use Descriptive Test Names

```php
// ✅ Good
it('validates email addresses correctly')

// ❌ Bad
it('test 1')
```

### 5. Test Edge Cases

```php
it('handles empty strings')
it('handles null values')
it('handles very long inputs')
it('handles special characters')
```

## Continuous Integration

### GitHub Actions Example

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    services:
      mariadb:
        image: mariadb:10.11
        env:
          MYSQL_ROOT_PASSWORD: root_password
          MYSQL_DATABASE: phpsimpl_test
          MYSQL_USER: test_user
          MYSQL_PASSWORD: test_pass
        ports:
          - 3306:3306
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install dependencies
        run: composer install
        
      - name: Run tests
        run: composer test
```

## Troubleshooting

### Tests Fail with "Connection Refused"

- Ensure Docker is running
- Wait longer for MariaDB to initialize
- Check `docker ps` to verify container is running

### "Table doesn't exist" Errors

- Run the database initialization script
- Verify you're using the correct database name

### Permission Denied for Cache Files

```bash
chmod -R 777 cache/
```

## Contributing

When contributing improvements to PHPSimpl:

1. **Write tests first** - Document expected behavior
2. **Run existing tests** - Ensure nothing breaks
3. **Add new tests** - For new features
4. **Update docs** - Keep this guide current

## Resources

- [PEST Documentation](https://pestphp.com)
- [PHPUnit Assertions](https://phpunit.de/assertions.html)
- [Mockery Documentation](http://docs.mockery.io)

---

**Questions?** Open an issue on GitHub or consult the [implementation plan](/.gemini/antigravity/brain/.../implementation_plan.md).
