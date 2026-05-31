#!/usr/bin/env bash
# =====================================================================
#  Compilation + lancement de l'application (Linux / macOS)
# =====================================================================
set -e
cd "$(dirname "${BASH_SOURCE[0]}")"

mkdir -p out

# Connecteur JDBC : tous les .jar présents dans lib/
CP="lib/*"

echo ">> Compilation des sources..."
find src -name "*.java" > sources.txt
javac -encoding UTF-8 -d out -cp "$CP" @sources.txt
rm -f sources.txt

echo ">> Lancement de l'application..."
java -cp "out:$CP" fr.blr.mediatheque.App
