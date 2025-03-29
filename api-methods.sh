#!/bin/bash

# Ensure jq is installed
if ! command -v jq &> /dev/null; then
    echo "jq is required but not installed. Please install it (e.g., 'sudo apt install jq')."
    exit 1
fi

# Get JWT Token
echo "Getting JWT Token..."
login_body='{"username":"test@example.com","password":"test"}'
token_response=$(curl -s -X POST "http://localhost:8080/api/login" \
    -H "Content-Type: application/json" \
    -d "$login_body")
token=$(echo "$token_response" | jq -r '.token')

if [ -z "$token" ] || [ "$token" == "null" ]; then
    echo "Failed to get JWT token. Response: $token_response"
    exit 1
fi

echo "$token"
headers="Authorization: Bearer $token"

echo "--------------------------------"
echo "GET /api/tasks (List Tasks)"
curl -s -X GET "http://localhost:8080/api/tasks?page=1&limit=2" \
    -H "$headers" | jq '.'
echo "--------------------------------"

echo "GET /api/tasks/1 (Show Task)"
curl -s -X GET "http://localhost:8080/api/tasks/1" \
    -H "$headers" | jq '.'
echo "--------------------------------"

echo "POST /api/tasks (Create Task)"
create_body='{"title":"Test Task","description":"This is a test","status":"todo"}'
curl -s -X POST "http://localhost:8080/api/tasks" \
    -H "$headers" \
    -H "Content-Type: application/json" \
    -d "$create_body" | jq '.'
echo "--------------------------------"

echo "PUT /api/tasks/1 (Update Task)"
update_body='{"title":"Updated Task","description":"Updated desc","status":"in_progress"}'
curl -s -X PUT "http://localhost:8080/api/tasks/1" \
    -H "$headers" \
    -H "Content-Type: application/json" \
    -d "$update_body" | jq '.'
echo "--------------------------------"

echo "DELETE /api/tasks/1 (Delete Task)"
curl -s -X DELETE "http://localhost:8080/api/tasks/1" \
    -H "$headers"
if [ $? -eq 0 ]; then
    echo "Delete successful (204 No Content)"
else
    echo "Delete failed"
fi
echo "--------------------------------"