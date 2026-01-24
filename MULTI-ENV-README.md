# Multi-Environment Testing - Quick Reference

## 🚀 Quick Start

```bash
# Test on PHP 5.5 (current production)
./test-php55.sh

# Test on PHP 8.2 (migration target)
./test-php82.sh

# Test BOTH environments
./test-all-environments.sh
```

## 📦 Environment Control

```bash
./env.sh 55      # Start PHP 5.5
./env.sh 82      # Start PHP 8.2  
./env.sh all     # Start both
./env.sh down    # Stop all
```

## 🐳 Docker Services

| Service | PHP Version | Extensions | Purpose |
|---------|-------------|------------|---------|
| php55 | 5.5.38 | mysql, mysqli, pdo | Current production baseline |
| php82 | 8.2.29 | mysqli, pdo | Migration target |
| mariadb | 10.11 | - | Shared test database |

## 📋 Expected Test Results

### PHP 5.5 (Current)
- ✅ Tests SHOULD PASS
- Uses `mysql_*` functions
- Validates current behavior

### PHP 8.2 (Target)
- ✅ Tests PASS
- Uses `mysqli` extension
- Full PEST test suite

## 📖 Full Documentation

- [MULTI-ENV-TESTING.md](MULTI-ENV-TESTING.md) - Complete guide
- [TESTING.md](TESTING.md) - General testing guide

## ⚙️ Manual Testing

```bash
# Access PHP 5.5 container
docker-compose exec php55 bash

# Access PHP 8.2 container
docker-compose exec php82 bash

# Run PEST tests (PHP 8.2 only - PEST requires PHP 7.3+)
docker-compose exec php82 ./vendor/bin/pest tests/Unit

# PHP 5.5 syntax validation
docker-compose exec php55 php -l lib/db.php
```

## 🎯 Migration Workflow

1. **Baseline** - Test on PHP 5.5 (current code)
2. **Migrate** - Update `mysql_*` → `mysqli_*`
3. **Verify** - Test on PHP 5.5 still passes
4. **Validate** - Test on PHP 8.2 now passes
5. **Compare** - Both environments identical behavior

---

For detailed information, see [MULTI-ENV-TESTING.md](MULTI-ENV-TESTING.md)
