# PHPSimpl Test Application

This test application demonstrates and verifies the functionality of the PHPSimpl framework.

## Quick Start

### 1. Start MariaDB Database

```bash
docker-compose up -d
```

This starts a MariaDB container with the test database pre-configured.

### 2. Initialize the Database

Wait a few seconds for MariaDB to initialize, then:

```bash
docker exec -i phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test < test-app/database.sql
```

### 3. Run the Test Application

```bash
cd test-app
php -S localhost:8000
```

Then visit: **http://localhost:8000**

## What the Test App Demonstrates

✅ **Database connectivity** - Connects to MariaDB  
✅ **Query execution** - SELECT, INSERT, UPDATE, DELETE  
✅ **Form handling** - Form class with validation  
✅ **Data validation** - Email, phone, URL patterns  
✅ **HTML escaping** - XSS prevention with h() function  
✅ **Error handling** - Graceful error display  

## Database Schema

The test database includes:

- **test_users** - Main user table with sample data
- **posts** - Posts table with foreign key relationship

Sample users are pre-loaded so you can see data immediately.

## Configuration

Database settings in `config.php`:

```php
define('DBHOST', '127.0.0.1:3307');
define('DBUSER', 'test_user');
define('DBPASS', 'test_pass');
define('DB_DEFAULT', 'phpsimpl_test');
```

## Testing the Current Behavior

Before making any improvements to PHPSimpl, run this app to:

1. **Document current behavior** - Take screenshots
2. **Test all features** - Click through everything
3. **Note any quirks** - Document unexpected behavior

After implementing improvements, run it again to verify **nothing broke**.

## Database Management

**Access MariaDB:**
```bash
docker exec -it phpsimpl_mariadb mysql -utest_user -ptest_pass phpsimpl_test
```

**Stop database:**
```bash
docker-compose down
```

**Reset database:**
```bash
docker-compose down -v  # Removes volumes
docker-compose up -d
# Re-run database.sql
```

## Troubleshooting

**Can't connect to database:**
- Wait 10-15 seconds after `docker-compose up`
- Check if container is running: `docker ps`
- View logs: `docker-compose logs`

**Port 3307 already in use:**
Change the port in `docker-compose.yml` and `config.php`

## Next Steps

Once you verify the test app works:

1. Run the automated test suite: `composer test`
2. Start implementing improvements
3. Re-run tests to ensure compatibility
4. Re-run this app to verify UI behavior

---

**Note**: This application uses the **current codebase** with deprecated `mysql_*` functions. It requires PHP 7+ but will need updates once we migrate to `mysqli_*`.
