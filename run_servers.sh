#!/bin/bash
set -e

echo "Starting Python AI server..."
cd /home/tf3k/ExportBridgeWeb_test/ai_service
python3 server.py &
PYTHON_PID=$!

echo "Waiting for Python server to start..."
sleep 3

echo "Starting Symfony server..."
cd /home/tf3k/ExportBridgeWeb_test
symfony server:start

# Cleanup
trap "kill $PYTHON_PID 2>/dev/null" EXIT
