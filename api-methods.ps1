# Get JWT Token
$loginBody = @{ username = "test@example.com"; password = "test" } | ConvertTo-Json
$tokenResponse = Invoke-RestMethod -Uri "http://localhost:8080/api/login" -Method Post -Body $loginBody -ContentType "application/json"
$token = $tokenResponse.token
Write-Host $token
$headers = @{ "Authorization" = "Bearer $token" }

Write-Host "GET /api/tasks (List Tasks)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks?page=1&limit=2" -Method Get -Headers $headers | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "GET /api/tasks/1 (Show Task)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Get -Headers $headers | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "POST /api/tasks (Create Task)"
$createBody = @{ title = "Test Task"; description = "This is a test"; status = "todo" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks" -Method Post -Body $createBody -ContentType "application/json" -Headers $headers | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "PUT /api/tasks/1 (Update Task)"
$updateBody = @{ title = "Updated Task"; description = "Updated desc"; status = "in_progress" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Put -Body $updateBody -ContentType "application/json" -Headers $headers | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "DELETE /api/tasks/1 (Delete Task)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Delete -Headers $headers
Write-Host "Delete successful (204 No Content)"
Write-Host "--------------------------------"