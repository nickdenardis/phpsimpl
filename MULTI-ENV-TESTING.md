# Multi-Environment Testing Guide

## Overview

PHPSimpl now supports testing across **two PHP environments** to ensure backward compatibility during modernization:

- **PHP 5.5** - Current production environment with `mysql_*` extension
- **PHP 8.2** - Target environment with `mysqli` extension

## Quick Start

### Start Specific Environment

```bash
# Start PHP 5.5 environment
./env.sh 55

# Start PHP 8.2 environment  
./env.sh 82

# Start both environments
./env.sh all

# Stop all services
./env.sh down
```

### Run Tests in Specific Environment

```bash
# Test on PHP 5.5 (legacy environment)
./test-php55.sh

# Test on PHP 8.2 (modern environment - runs PEST)
./test-php82.sh

# Test on BOTH (comprehensive validation)
./test-all-environments.sh
```

## Docker Architecture

### Services

**mariadb** - MariaDB 10.11 database (shared by all environments)
- Port: 3307
- Database: phpsimpl_test
- User: test_user / test_pass

**php55** - PHP 5.5.38-cli container
- Extensions: mysql, mysqli, pdo, pdo_mysql
- Profile: `php55`
- Tests current production code

**php82** - PHP 8.2-cli container  
- Extensions: mysqli, pdo, pdo_mysql
- Profile: `php82`
- Tests migrated code

### Docker Compose Profiles

Profiles allow running specific PHP versions:

```bash
# Start only PHP 5.5
docker-compose --profile php55 up -d

# Start only PHP 8.2
docker-compose --profile php82 up -d

# Start both
docker-compose --profile all up -d
```

## Testing Workflow

### 1. Baseline (Current Code on PHP 5.5)

```bash
# Start PHP 5.5 environment
./env.sh 55

# Run tests
./test-php55.sh
```

**Expected Result**: Tests should PASS (baseline behavior)

### 2. After Migration (New Code on PHP 8.2)

```bash
# Start PHP 8.2 environment
./env.sh 82

# Run tests
./test-php82.sh
```

**Expected Result**: Tests should PASS (same behavior on new PHP)

### 3. Regression Testing (Both Environments)

```bash
# Test both environments simultaneously
./test-all-environments.sh
```

**Expected Result**: Both environments pass with identical behavior

## Manual Testing

### Access PHP Container

```bash
# PHP 5.5 container
docker-compose exec php55 bash

# PHP 8.2 container
docker-compose exec php82 bash
```

### Run Commands in Container

```bash
# Check PHP version
docker-compose exec php55 php -v
docker-compose exec php82 php -v

# Run PEST tests (PHP 8.2 only - PEST requires PHP 7.3+)
docker-compose exec php82 ./vendor/bin/pest tests/Unit/DBTest.php
docker-compose exec php82 ./vendor/bin/pest tests/Feature/DatabaseTest.php

# PHP 5.5 syntax validation (smoke test)
docker-compose exec php55 php -l lib/db.php

# Interactive shell
docker-compose exec php82 bash
root@container:/app# ./vendor/bin/pest
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Multi-Environment Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['5.5', '8.2']
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Start environment
        run: |
          docker-compose --profile php${{ matrix.php-version }} up -d
          
      - name: Run tests
        run: ./test-php${{ matrix.php-version }}.sh
```

## Troubleshooting

### Port Conflicts

If port 3307 is in use:
```yaml
# In docker-compose.yml, change:
ports:
  - "3308:3306"  # Use different host port
```

### Container Won't Start

```bash
# View logs
docker-compose logs php55
docker-compose logs php82
docker-compose logs mariadb

# Rebuild images
docker-compose --profile all build --no-cache
```

### Tests Fail in Container but Pass Locally

Check environment variables:
```bash
# Inside container
docker-compose exec php55 env | grep DB
```

### Database Connection Issues

```bash
# Verify MariaDB is running
docker ps | grep mariadb

# Test connection
docker exec -it phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test -e "SELECT 1"
```

## Best Practices

### 1. Always Test Both Environments

Before submitting a PR:
```bash
./test-all-environments.sh
```

### 2. Document Environment-Specific Behavior

If a test behaves differently between PHP versions, document it:
```php
it('handles feature X')->skip(
    PHP_VERSION_ID < 70000, 
    'Feature requires PHP 7+'
);
```

### 3. Use Environment Variables

Don't hardcode database hosts:
```php
// ✅ Good
$host = getenv('DBHOST') ?: 'localhost';

// ❌ Bad
$host = 'mariadb';
```

### 4. Clean Up After Tests

```bash
# Stop all services
docker-compose down

# Remove volumes (fresh start)
docker-compose down -v
```

## Script Reference

| Script | Purpose |
|--------|---------|
| `env.sh` | Quick environment switcher |
| `test-php55.sh` | Run tests in PHP 5.5 |
| `test-php82.sh` | Run tests in PHP 8.2 |
| `test-all-environments.sh` | Run tests in both environments |

## Environment Variables

| Variable | PHP 5.5 | PHP 8.2 | Description |
|----------|---------|---------|-------------|
| DBHOST | `mariadb` | `mariadb` | Database host |
| DBUSER | `test_user` | `test_user` | Database user |
| DBPASS | `test_pass` | `test_pass` | Database password |
| DB_DEFAULT | `phpsimpl_test` | `phpsimpl_test` | Default database |

## Migration Strategy

### Phase 1: Baseline ✅
- [x] Set up PHP 5.5 environment
- [x] Run all tests on PHP 5.5
- [x] Document current behavior

### PHP 8.2 (Target)
- ✅ Tests PASS
- Uses `mysqli` extension
- Full PEST test suite
- [ ] Run tests on PHP 5.5 (should still pass)
- [ ] Run tests on PHP 8.2 (should now pass)

### Phase 3: Validation ⏳
- [ ] Compare results between environments
- [ ] Fix any discrepancies
- [ ] Achieve 100% test parity

---

**Next Steps**: Run `./test-all-environments.sh` to see the current state across both PHP versions.
