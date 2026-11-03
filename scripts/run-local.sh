#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
export PHPRC="$PWD/.local-runtime/php.ini"
exec "${PHP_BIN:-$PWD/.local-runtime/php}" artisan serve --env=local --host=127.0.0.1 --port="${PORT:-8000}"
