@echo off
echo Pushing to GitHub...

git add .

set /p msg=type your message : 

git commit -m "%msg%"

git push origin main

echo.
echo Push completed!
pause