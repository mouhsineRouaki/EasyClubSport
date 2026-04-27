#!/bin/sh

set -e

if [ ! -d /app/node_modules ] || [ -z "$(ls -A /app/node_modules 2>/dev/null)" ]; then
  echo "Installation des dependances npm..."
  npm install
fi

echo "Demarrage du frontend Vue..."
exec npm run dev -- --host 0.0.0.0 --port 5173
