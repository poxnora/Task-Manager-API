Write-Host "GET /api/tasks (List Tasks with Pagination)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks?page=1&limit=2" -Method Get | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "GET /api/tasks/1 (Show Task)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Get | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "POST /api/tasks (Create Task)"
$createBody = @{ title = "Test Task"; description = "This is a test"; status = "todo" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks" -Method Post -Body $createBody -ContentType "application/json" | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "PUT /api/tasks/1 (Update Task)"
$updateBody = @{ title = "Updated Task"; description = "Updated desc"; status = "in_progress" } | ConvertTo-Json
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Put -Body $updateBody -ContentType "application/json" | ConvertTo-Json -Depth 10
Write-Host "--------------------------------"

Write-Host "DELETE /api/tasks/1 (Delete Task)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/1" -Method Delete
Write-Host "Delete successful (204 No Content)"
Write-Host "--------------------------------"

Write-Host "Error Cases"
Write-Host "GET /api/tasks/999 (Non-existent)"
Invoke-RestMethod -Uri "http://localhost:8080/api/tasks/999" -Method Get -ErrorAction SilentlyContinue | ConvertTo-Json -Depth 10
if ($LASTEXITCODE -ne 0) { Write-Host "Error: $($Error[0].Exception.Message)" }
Write-Host "--------------------------------"