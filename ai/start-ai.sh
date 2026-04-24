#!/bin/bash

# Get the directory of the script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$DIR"

if [ ! -f "bin/llama-server" ]; then
    echo "Error: llama-server not found in bin directory."
    echo "Please run 'php setup-ai.php' first."
    exit 1
fi

MODEL_FILE=$(ls models/*.gguf 2>/dev/null | head -n 1)

if [ -z "$MODEL_FILE" ]; then
    echo "Error: No .gguf model file found in models directory."
    echo "Please run 'php setup-ai.php' first."
    exit 1
fi

chmod +x bin/llama-server

echo "Starting FreshLeaf Local AI Server..."
echo "Model: $MODEL_FILE"
echo "Port: 9000"
echo "Context Size: 2048"

./bin/llama-server \
  -m "$MODEL_FILE" \
  -c 2048 \
  --port 9000 \
  --no-mmap
