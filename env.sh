#!/bin/bash
# Quick environment switcher

case "$1" in
  "55"|"5.5"|"php55")
    echo "🐘 Starting PHP 5.5 environment..."
    docker-compose --profile php55 up -d
    echo "✅ PHP 5.5 ready. Run: docker-compose exec php55 bash"
    ;;
  "82"|"8.2"|"php82")
    echo "🐘 Starting PHP 8.2 environment..."
    docker-compose --profile php82 up -d
    echo "✅ PHP 8.2 ready. Run: docker-compose exec php82 bash"
    ;;
  "all")
    echo "🐘 Starting ALL environments..."
    docker-compose --profile all up -d
    echo "✅ All environments ready"
    ;;
  "down")
    echo "🛑 Stopping all services..."
    docker-compose down
    ;;
  *)
    echo "Usage: ./env.sh [55|82|all|down]"
    echo ""
    echo "Examples:"
    echo "  ./env.sh 55    - Start PHP 5.5 + MariaDB"
    echo "  ./env.sh 82    - Start PHP 8.2 + MariaDB"
    echo "  ./env.sh all   - Start both PHP environments"
    echo "  ./env.sh down  - Stop all services"
    exit 1
    ;;
esac
