## Description

A PHP framework for rapidly creating classes that mirror MySQL tables. The Simpl framework comes with the basic functions to list, display and edit records from the database. With this framework a simple manager and front of a site can be created within a few minutes. An example database, manager and front end are included with the framework.

## Features

* No make/PEAR/Root Access required
* Database table abstraction
* Automatically configured database functions (ie. !GetInfo, !GetList, Save, Delete, Search and Form)
* Advanced automatic form creation to mirror database/class expectations, XHTML compliant and ADA accessible.
* Table structure caching, Query level caching, and saving of inserts or updates to the filesystem if the database is unavailable.
* Ability to query cross databases on the same server
* Form validation
* Upload, Move, Copy, Delete and List Files
* Resize and Rotate Images
* Email with class abstraction with support for attachments
* RSS0.91, RSS1.0, RSS2.0, ATOM Feeds
* CVS, JSON, XML and SQL Exports
* JSON encoding and decoding support

## Goals

* *Easy Install*. _No need to be an administrator._
* *Minimal Server Load*. _Created for a high traffic shared server environment._
* *Straightforward API*. _No need to dig through documentation, functions are naturally named._
* *Stop Wasting Time*. _Time is precious, no need to reinvent the wheel._

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