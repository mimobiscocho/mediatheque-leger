#!/usr/bin/env bash
# =============================================================================
#  Démarre le client léger (PHP) en arrière-plan.
#  - Charge la config depuis ../../.env si présent
#  - Active pdo_mysql/mysqli si nécessaire (sans toucher au php.ini système)
#  - Stocke le PID dans .pid, les logs dans logs/server.log
# =============================================================================
set -e
cd "$(dirname "$0")"

# --- 1. Chargement de la configuration -------------------------------------
PROJET_ROOT="$(cd ../.. && pwd)"
if [ -f "$PROJET_ROOT/.env" ]; then
  set -a
  # shellcheck disable=SC1091
  source "$PROJET_ROOT/.env"
  set +a
fi
: "${DB_HOST:=localhost}"
: "${DB_NAME:=mediatheque}"
: "${DB_USER:=mediatheque}"
: "${DB_PASSWORD:=mediatheque}"
: "${LEGER_PORT:=8000}"

# --- 2. Vérification des prérequis -----------------------------------------
if ! command -v php >/dev/null 2>&1; then
  echo "Erreur : PHP n'est pas installé." >&2
  exit 1
fi

# --- 3. Activation à la volée des extensions manquantes --------------------
PHP_FLAGS=()
if ! php -m 2>/dev/null | grep -qx "pdo_mysql"; then
  PHP_FLAGS+=("-d" "extension=pdo_mysql")
fi
if ! php -m 2>/dev/null | grep -qx "mysqli"; then
  PHP_FLAGS+=("-d" "extension=mysqli")
fi

# --- 4. Si déjà lancé, on n'en relance pas un deuxième ---------------------
mkdir -p logs
if [ -f .pid ] && kill -0 "$(cat .pid 2>/dev/null)" 2>/dev/null; then
  echo "Client léger déjà démarré (PID $(cat .pid))."
  echo "URL : http://localhost:$LEGER_PORT"
  exit 0
fi
rm -f .pid

# Port libre ?
if command -v ss >/dev/null && ss -ltn 2>/dev/null | awk '{print $4}' | grep -q ":$LEGER_PORT$"; then
  echo "Erreur : le port $LEGER_PORT est déjà utilisé." >&2
  exit 1
fi

# --- 5. Démarrage en arrière-plan ------------------------------------------
export DB_HOST DB_NAME DB_USER
export DB_PASS="$DB_PASSWORD"   # nom utilisé par config/config.php

echo "Démarrage du client léger sur http://localhost:$LEGER_PORT ..."
PHP_CLI_SERVER_WORKERS=4 nohup php "${PHP_FLAGS[@]}" \
  -S "localhost:$LEGER_PORT" -t public \
  > logs/server.log 2>&1 &
echo $! > .pid

# --- 6. Vérification du démarrage ------------------------------------------
sleep 1
if kill -0 "$(cat .pid)" 2>/dev/null; then
  echo "OK : PID $(cat .pid) — logs dans logs/server.log"
  echo "URL : http://localhost:$LEGER_PORT"
else
  echo "Échec du démarrage. Dernières lignes du log :" >&2
  tail -20 logs/server.log >&2
  rm -f .pid
  exit 1
fi
