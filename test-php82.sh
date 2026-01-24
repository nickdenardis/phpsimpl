#!/bin/bash
# Test runner for PHP 8.2 environment (migration target)

set -e

echo "=================================================="
echo "Running tests on PHP 8.2 (Migration Target)"
echo "=================================================="

# Ensure MariaDB and PHP 8.2 containers are running
docker-compose --profile php82 up -d mariadb php82

echo "Waiting for MariaDB to be ready..."
sleep 5

# Initialize database if needed
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql 2>/dev/null || true

echo ""
echo "Running PEST tests in PHP 8.2 container..."
echo ""

# Run tests in PHP 8.2 container with correct DB connection for container
docker-compose exec -e DB_HOST=mariadb -e DB_PORT=3306 php82 ./vendor/bin/pest "$@"

echo ""
echo "=================================================="
echo "PHP 8.2 Tests Complete"
echo "=================================================="
