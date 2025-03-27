#!/bin/sh
until pg_isready -h localhost -p 5432 -U user -d task_manager; do
  echo "Waiting for Postgres..."
  sleep 2
done
echo "Postgres is ready!"