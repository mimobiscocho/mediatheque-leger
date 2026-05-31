@echo off
REM =====================================================================
REM  Compilation + lancement de l'application (Windows)
REM =====================================================================
cd /d "%~dp0"

if not exist out mkdir out

echo >> Compilation des sources...
dir /s /b src\*.java > sources.txt
javac -encoding UTF-8 -d out -cp "lib/*" @sources.txt
del sources.txt

echo >> Lancement de l'application...
java -cp "out;lib/*" fr.blr.mediatheque.App
