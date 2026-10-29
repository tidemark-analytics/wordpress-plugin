#!/usr/bin/env bash
# Builds tidemark-analytics.zip with the plugin folder at its root, ready to upload to WordPress.
set -euo pipefail
cd "$(dirname "$0")"

rm -f tidemark-analytics.zip
zip -rq tidemark-analytics.zip tidemark-analytics -x '*.DS_Store'
echo "Wrote tidemark-analytics.zip"
