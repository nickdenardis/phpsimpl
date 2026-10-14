#!/bin/bash
# Run tests on BOTH PHP 5.5 and PHP 8.2 to verify compatibility

set -e

echo "======================================================================="
echo "🧪 MULTI-ENVIRONMENT TEST SUITE"
echo "======================================================================="
echo ""
echo "This will run tests on both PHP 5.5 (current) and PHP 8.2 (target)"
echo "to ensure backward compatibility during migration."
echo ""

# Start all services
echo "Starting all Docker services..."
docker-compose --profile all up -d

echo "Waiting for services to be ready..."
sleep 10

# Initialize database
echo "Initializing database..."
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql 2>/dev/null || true

echo ""
echo "======================================================================="
echo "📦 PHP 5.5 Environment (Current Production - mysql_* extension)"
echo "======================================================================="
echo ""

docker-compose exec php55 php -v
echo ""
echo "Running syntax validation (PEST requires PHP 7.3+)..."
docker-compose exec php55 find lib/ -name "*.php" -exec php -l {} \; || echo "⚠️  PHP 5.5 syntax errors found"

echo ""
echo "======================================================================="
echo "📦 PHP 8.2 Environment (Migration Target - mysqli extension)"  
echo "======================================================================="
echo ""

docker-compose exec php82 php -v
echo ""
if [ -n "$USE_CACHE" ]; then
    echo "Running tests with USE_CACHE=$USE_CACHE..."
    docker-compose exec -e DB_HOST=mariadb -e DB_PORT=3306 -e USE_CACHE="$USE_CACHE" php82 ./vendor/bin/pest --colors=always "$@" || echo "⚠️  Some PHP 8.2 tests failed"
else
    echo "1/2 Running tests with USE_CACHE=false (cache disabled)..."
    docker-compose exec -e DB_HOST=mariadb -e DB_PORT=3306 -e USE_CACHE=false php82 ./vendor/bin/pest --colors=always "$@" || echo "⚠️  Some PHP 8.2 tests failed"
    echo ""
    echo "2/2 Running tests with USE_CACHE=true (cache enabled)..."
    docker-compose exec -e DB_HOST=mariadb -e DB_PORT=3306 -e USE_CACHE=true php82 ./vendor/bin/pest --colors=always "$@" || echo "⚠️  Some PHP 8.2 tests failed"
fi

echo ""
echo "======================================================================="
echo "✅ Multi-Environment Test Suite Complete"
echo "======================================================================="
echo ""
echo "Compare the results above to ensure compatibility between versions."
echo ""
