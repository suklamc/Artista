#!/usr/bin/env bash
# Build installable zips into dist/:
#   plg_system_sunfw.zip, tpl_jsn_artista2_pro.zip and pkg_artista_v<version>.zip (install this one).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
SRC="$ROOT/src"
DIST="$ROOT/dist"
VERSION="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$SRC/pkg_artista.xml" | head -1)"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

rm -rf "$DIST" && mkdir -p "$DIST" "$TMP/pkg/packages"

# Never ship per-site generated files.
EXCLUDES=(-x '*.DS_Store' -x '*/css/custom/custom.css' -x '*/backups/*')

(cd "$SRC/plugins/system/sunfw" && zip -qr -X "$TMP/pkg/packages/plg_system_sunfw.zip" . "${EXCLUDES[@]}")
(cd "$SRC/templates/jsn_artista2_pro" && zip -qr -X "$TMP/pkg/packages/tpl_jsn_artista2_pro.zip" . "${EXCLUDES[@]}" \
	-x 'css/color_[0-9a-f]*.css' -x 'css/core/*_[0-9a-f]*.css')

cp "$SRC/pkg_artista.xml" "$SRC/script.php" "$TMP/pkg/"
cp "$TMP/pkg/packages/"*.zip "$DIST/"
(cd "$TMP/pkg" && zip -qr -X "$DIST/pkg_artista_v${VERSION}.zip" .)

(cd "$DIST" && sha512sum "pkg_artista_v${VERSION}.zip" > "pkg_artista_v${VERSION}.zip.sha512")
echo "Built Artista ${VERSION}:"
ls -la "$DIST"
