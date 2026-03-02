#!/bin/bash
# Start the test application on PHP 8.2 with built-in web server (detached)

set -e

echo "🚀 Starting test application on PHP 8.2..."

# Ensure environment is running
docker-compose --profile php82 up -d mariadb php82

echo "Waiting for services to be ready..."
sleep 8

# Initialize database
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql 2>/dev/null || true

echo ""
echo "Starting PHP 8.2 built-in web server in background..."

# Start PHP web server in the container (detached)
docker exec -d phpsimpl_php82 sh -c "cd test-app && php -S 0.0.0.0:8000"

echo ""
echo "✅ Test application is running!"
echo ""
echo "   📱 Access at: http://localhost:8082"
echo "   🐘 PHP Version: 8.2.29"
echo "   ⚠️  Will fail (no mysql_* extension)"
echo ""
echo "To stop: docker-compose down"
