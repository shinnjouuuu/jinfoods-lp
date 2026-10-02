#!/usr/bin/env bash
# テーマを Local のサイトへ配置する。
#   ./install-to-local.sh <サイト名>
# 例) ./install-to-local.sh jinfoods
#
# Local のサイトは ~/Local Sites/<サイト名>/app/public に置かれる。
set -euo pipefail

SITE="${1:-}"
if [ -z "$SITE" ]; then
  echo "使い方: $0 <Localのサイト名>"
  echo "利用できるサイト:"
  ls ~/Local\ Sites 2>/dev/null | sed 's/^/  /'
  exit 1
fi

SRC="$(cd "$(dirname "$0")" && pwd)/jinfoods"
DEST="$HOME/Local Sites/$SITE/app/public/wp-content/themes/jinfoods"

if [ ! -d "$HOME/Local Sites/$SITE/app/public" ]; then
  echo "✗ サイトが見つかりません: $SITE"
  echo "  Local アプリで先にサイトを作ってください。"
  exit 1
fi

rm -rf "$DEST"
mkdir -p "$DEST"
# .DS_Store などは持っていかない
rsync -a --exclude='.DS_Store' "$SRC/" "$DEST/"

echo "✓ 配置しました: $DEST"
echo "  Local でサイトを起動し、外観 > テーマ から「JIN foods」を有効化してください。"
