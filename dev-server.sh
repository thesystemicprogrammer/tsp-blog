#!/bin/bash

# Local Development Server for Hugo Blog with PHP API
# This script runs both Hugo and PHP servers simultaneously

echo "🚀 Starting Local Development Servers..."
echo ""

# Check if Hugo is installed
if ! command -v hugo &> /dev/null; then
    echo "❌ Hugo is not installed. Please install Hugo first."
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP first."
    exit 1
fi

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Stopping servers..."
    kill $HUGO_PID $PHP_PID 2>/dev/null
    exit
}

trap cleanup EXIT INT TERM

# Start Hugo server
echo "📝 Starting Hugo server on http://localhost:1313"
hugo server --port 1313 --bind 0.0.0.0 --buildDrafts &
HUGO_PID=$!

# Wait for Hugo to start
sleep 2

# Start PHP server for API
echo "🐘 Starting PHP server on http://localhost:8000"
cd static && php -S localhost:8000 &
PHP_PID=$!

echo ""
echo "✅ Servers running:"
echo "   Hugo:  http://localhost:1313"
echo "   API:   http://localhost:8000/api/counter/count.php"
echo ""
echo "⚠️  Note: View counter will NOT work in local development"
echo "   Reason: Frontend calls /api/counter/count.php (production path)"
echo "   Solution: Use mock mode or test on production after deployment"
echo ""
echo "Press Ctrl+C to stop both servers"

# Wait for both processes
wait
