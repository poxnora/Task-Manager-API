#!/bin/sh
# Check if pg_isready is available
if ! command -v pg_isready > /dev/null; then
  echo "Error: pg_isready not found. Please ensure postgresql-client is installed."
  exit 1
fi

# Wait for Postgres to be ready
until pg_isready -h localhost -p 5432 -U user -d task_manager; do
  echo "Waiting for Postgres to be ready..."
  sleep 2
done
echo "Postgres is up and ready!"