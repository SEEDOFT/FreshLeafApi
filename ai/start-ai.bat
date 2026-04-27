@echo off
setlocal enabledelayedexpansion

cd /d "%~dp0"

if not exist "bin\llama-server.exe" (
    echo Error: llama-server.exe not found in bin directory.
    echo Please run 'php setup-ai.php' first.
    pause
    exit /b 1
)

set "MODEL_FILE="
for %%f in (models\*.gguf) do (
    set "MODEL_FILE=%%f"
    goto :found
)

:found
if "%MODEL_FILE%"=="" (
    echo Error: No .gguf model file found in models directory.
    echo Please run 'php setup-ai.php' first.
    pause
    exit /b 1
)

echo Starting FreshLeaf Local AI Server...
echo Model: %MODEL_FILE%
echo Port: 9000
echo Context Size: 4096

"bin\llama-server.exe" ^
  -m "%MODEL_FILE%" ^
  -c 4096 ^
  --port 9000 ^
  --no-mmap

pause
