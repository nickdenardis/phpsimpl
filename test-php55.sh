#!/bin/bash
# Test runner for PHP 5.5 environment (legacy validation)
# NOTE: PEST requires PHP 7.3+ and cannot run on PHP 5.5
# This script validates syntax compatibility and runs smoke tests

set -e

echo "=================================================="
echo "Running validation on PHP 5.5 (Legacy Environment)"
echo "=================================================="

# Ensure MariaDB and PHP 5.5 containers are running
docker-compose --profile php55 up -d mariadb php55

echo "Waiting for MariaDB to be ready..."
sleep 5

# Initialize database if needed
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql 2>/dev/null || true

echo ""
echo "Running PHP syntax validation..."
echo ""

# Validate syntax of all PHP files in lib/
docker-compose exec php55 find lib/ -name "*.php" -exec php -l {} \;

echo ""
echo "Running smoke test via built-in server..."
echo ""

# Start PHP built-in server in background and test
docker-compose exec -d php55 php -S 0.0.0.0:8000 -t test-app
sleep 2

# Test the endpoint
if curl -s http://localhost:8055/index.php | grep -q "Database connection successful"; then
    echo "✓ Smoke test PASSED - Database connection works on PHP 5.5"
else
    echo "✗ Smoke test FAILED"
    exit 1
fi

echo ""
echo "=================================================="
echo "PHP 5.5 Validation Complete"
echo "=================================================="
