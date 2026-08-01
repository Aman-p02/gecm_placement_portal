@echo off
echo Pushing to GitHub...

git add .

set /p msg=added admin super admin student dashboard: 

git commit -m "%msg%"

git push origin main

echo.
echo Push completed!
pause