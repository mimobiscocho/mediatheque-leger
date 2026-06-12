#!/usr/bin/env bash
# =============================================================================
#  Médiathèque léger — Déploiement sur un serveur Linux
#
#  Cible :   Debian/Ubuntu (testé), RHEL/Fedora/Rocky (best-effort)
#  Stack :   Apache 2.4 + mod_php + MariaDB + extensions PHP (pdo_mysql, mbstring)
#  Effet :   - installe les paquets nécessaires
#            - crée la base + un utilisateur dédié + charge le schéma
#            - copie l'application dans INSTALL_DIR avec les bonnes permissions
#            - écrit un vhost Apache sécurisé (headers, refus app/config/logs)
#            - active le vhost et recharge Apache
#            - (option) active HTTPS via Let's Encrypt
#
#  Usage :   sudo ./deploy.sh [all|prereqs|database|app|vhost|tls|verify|status|help]
#
#  Variables surchageables :
#    INSTALL_DIR=/var/www/mediatheque-leger     répertoire d'installation
#    DOMAIN=mediatheque.example.fr              nom de domaine du vhost
#    DB_NAME=mediatheque                        nom de la base
#    DB_USER=mediatheque                        utilisateur BD
#    DB_PASSWORD=...                            mdp (généré aléatoirement sinon)
#    ADMIN_EMAIL=vous@example.fr                pour HTTPS / vhost
#    DB_REMOTE_ACCESS=no                        "yes" pour autoriser une connexion BD distante
#
#  Re-run safe : le script est idempotent (CREATE OR ALTER, rsync, a2ensite...).
# =============================================================================
set -euo pipefail

# --- Couleurs ---------------------------------------------------------------
if [ -t 1 ]; then
  C_OK=$'\033[0;32m'; C_KO=$'\033[0;31m'
  C_WARN=$'\033[0;33m'; C_INFO=$'\033[0;34m'
  C_DIM=$'\033[0;90m'; C_RESET=$'\033[0m'
else
  C_OK=''; C_KO=''; C_WARN=''; C_INFO=''; C_DIM=''; C_RESET=''
fi

log()   { printf "${C_INFO}>>${C_RESET} %s\n" "$*"; }
ok()    { printf "  ${C_OK}[OK]${C_RESET} %s\n" "$*"; }
warn()  { printf "  ${C_WARN}[!]${C_RESET}  %s\n" "$*"; }
err()   { printf "  ${C_KO}[KO]${C_RESET} %s\n" "$*" >&2; }
title() { printf "\n${C_INFO}=== %s ===${C_RESET}\n" "$*"; }

# --- sudo / root ------------------------------------------------------------
SUDO=""
if [ "$(id -u)" -ne 0 ]; then
  if command -v sudo >/dev/null 2>&1; then
    SUDO="sudo"
  else
    err "Ce script doit être exécuté en root (ou via sudo)."
    exit 1
  fi
fi

# --- Détection de la distribution -------------------------------------------
detect_distro() {
  if [ -f /etc/os-release ]; then
    . /etc/os-release
    case "${ID:-}${ID_LIKE:-}" in
      *debian*|*ubuntu*) echo "debian"; return ;;
      *rhel*|*fedora*|*centos*|*rocky*|*alma*) echo "rhel"; return ;;
      *arch*|*manjaro*) echo "arch"; return ;;
    esac
  fi
  echo "unknown"
}
DISTRO=$(detect_distro)

# --- Valeurs par défaut et état persistant ----------------------------------
STATE_DIR=/etc/mediatheque-leger
STATE_FILE="$STATE_DIR/deploy.state"

# Si un déploiement précédent a stocké des valeurs, on les recharge.
if [ -r "$STATE_FILE" ]; then
  # shellcheck disable=SC1090
  . "$STATE_FILE"
fi

INSTALL_DIR="${INSTALL_DIR:-/var/www/mediatheque-leger}"
DOMAIN="${DOMAIN:-$(hostname -f 2>/dev/null || hostname)}"
DB_HOST_BIND="${DB_HOST_BIND:-127.0.0.1}"
DB_NAME="${DB_NAME:-mediatheque}"
DB_USER="${DB_USER:-mediatheque}"
DB_PASSWORD="${DB_PASSWORD:-}"
ADMIN_EMAIL="${ADMIN_EMAIL:-webmaster@$DOMAIN}"
PHP_USER="${PHP_USER:-www-data}"
PHP_GROUP="${PHP_GROUP:-www-data}"
DB_REMOTE_ACCESS="${DB_REMOTE_ACCESS:-no}"

# Sur RHEL/Fedora, l'utilisateur web est "apache".
if [ "$DISTRO" = "rhel" ] && [ "$PHP_USER" = "www-data" ]; then
  PHP_USER=apache
  PHP_GROUP=apache
fi

# --- Détection du dossier source --------------------------------------------
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
if [ -f "$SCRIPT_DIR/public/index.php" ]; then
  SOURCE_DIR="$SCRIPT_DIR"
elif [ -f "$SCRIPT_DIR/mediatheque-leger-main/mediatheque-leger-main/public/index.php" ]; then
  SOURCE_DIR="$SCRIPT_DIR/mediatheque-leger-main/mediatheque-leger-main"
elif [ -f "$SCRIPT_DIR/mediatheque-leger-main/public/index.php" ]; then
  SOURCE_DIR="$SCRIPT_DIR/mediatheque-leger-main"
else
  err "Impossible de localiser les sources du client léger (public/index.php manquant)."
  exit 1
fi

# --- Persistance de l'état --------------------------------------------------
save_state() {
  $SUDO mkdir -p "$STATE_DIR"
  $SUDO tee "$STATE_FILE" >/dev/null <<EOF
# Généré par deploy.sh le $(date '+%Y-%m-%d %H:%M:%S')
INSTALL_DIR="$INSTALL_DIR"
DOMAIN="$DOMAIN"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASSWORD="$DB_PASSWORD"
DB_HOST_BIND="$DB_HOST_BIND"
DB_REMOTE_ACCESS="$DB_REMOTE_ACCESS"
PHP_USER="$PHP_USER"
PHP_GROUP="$PHP_GROUP"
ADMIN_EMAIL="$ADMIN_EMAIL"
EOF
  $SUDO chmod 600 "$STATE_FILE"
}

# =============================================================================
#                         ÉTAPES INDIVIDUELLES
# =============================================================================

# ----- 1. Paquets prérequis -------------------------------------------------
install_prereqs() {
  title "Installation des paquets"
  case "$DISTRO" in
    debian)
      $SUDO apt-get update -qq
      DEBIAN_FRONTEND=noninteractive $SUDO apt-get install -y --no-install-recommends \
          apache2 mariadb-server \
          php php-cli php-mysql php-mbstring php-xml php-curl \
          libapache2-mod-php \
          curl rsync openssl ca-certificates
      $SUDO systemctl enable --now apache2 mariadb
      $SUDO a2enmod rewrite headers >/dev/null 2>&1 || true
      ok "Paquets installés et services activés."
      ;;
    rhel)
      $SUDO dnf install -y --allowerasing \
          httpd mariadb-server \
          php php-cli php-mysqlnd php-mbstring php-xml \
          curl rsync openssl mod_ssl
      $SUDO systemctl enable --now httpd mariadb
      ok "Paquets installés et services activés."
      ;;
    arch)
      $SUDO pacman -Sy --noconfirm apache mariadb php php-apache curl rsync openssl
      [ -d /var/lib/mysql/mysql ] || $SUDO mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
      $SUDO systemctl enable --now httpd mariadb
      warn "Arch détecté — la configuration Apache+PHP est partielle, vérifiez manuellement /etc/httpd/conf/httpd.conf."
      ;;
    *)
      err "Distribution non supportée. Installez manuellement Apache, PHP (pdo_mysql, mbstring) et MariaDB puis relancez avec : DISTRO=debian $0 database"
      exit 1
      ;;
  esac
}

# ----- 2. Base de données ---------------------------------------------------
setup_database() {
  title "Configuration de la base de données"

  # Génère un mot de passe robuste si aucun n'est défini
  if [ -z "$DB_PASSWORD" ]; then
    DB_PASSWORD="$(openssl rand -base64 24 | tr -d '/+=' | cut -c1-24)"
    log "Mot de passe BD généré : $DB_PASSWORD"
  fi

  # Vérification que mariadb est joignable en root via socket Unix.
  if ! $SUDO mariadb -e "SELECT 1" >/dev/null 2>&1 \
       && ! $SUDO mysql -e "SELECT 1" >/dev/null 2>&1; then
    err "Impossible de se connecter à MariaDB en root (socket Unix)."
    err "Vérifiez que mariadb-server est démarré : systemctl status mariadb"
    exit 1
  fi

  # Choisit la commande disponible (mariadb sur Arch/récents, mysql sinon).
  MYSQL=mysql
  command -v mariadb >/dev/null && MYSQL=mariadb

  # Création idempotente (ALTER USER pour rafraîchir le mdp si déjà existant).
  $SUDO "$MYSQL" <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
CREATE USER IF NOT EXISTS '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';
ALTER  USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
ALTER  USER '$DB_USER'@'127.0.0.1' IDENTIFIED BY '$DB_PASSWORD';

GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'127.0.0.1';
FLUSH PRIVILEGES;
SQL
  ok "Base + utilisateur '$DB_USER' configurés."

  # Chargement du schéma s'il n'est pas déjà chargé (test : présence d'au moins
  # une table métier comme 'agent').
  local nb_tables
  nb_tables=$($SUDO "$MYSQL" -sN -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME';")
  if [ "${nb_tables:-0}" -lt 11 ]; then
    log "Chargement du schéma SQL ($SOURCE_DIR/sql/schema.sql)..."
    $SUDO "$MYSQL" "$DB_NAME" < "$SOURCE_DIR/sql/schema.sql"
    ok "Schéma chargé ($(grep -c '^CREATE TABLE' "$SOURCE_DIR/sql/schema.sql") tables, triggers et jeu de démonstration)."
  else
    ok "Schéma déjà présent ($nb_tables tables) — pas de rechargement."
  fi

  # Option : autoriser les connexions BD distantes (pour le client lourd Java).
  if [ "$DB_REMOTE_ACCESS" = "yes" ]; then
    title "Activation de l'accès BD distant"
    warn "Vous ouvrez MariaDB sur le réseau : assurez-vous d'avoir un pare-feu."
    $SUDO "$MYSQL" <<SQL
CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';
ALTER  USER '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'%';
FLUSH PRIVILEGES;
SQL
    # Bind sur 0.0.0.0 dans la config MariaDB
    local mycnf
    for f in /etc/mysql/mariadb.conf.d/50-server.cnf \
             /etc/mysql/my.cnf \
             /etc/my.cnf.d/server.cnf \
             /etc/my.cnf; do
      if [ -f "$f" ]; then mycnf="$f"; break; fi
    done
    if [ -n "${mycnf:-}" ]; then
      if $SUDO grep -qE "^\s*bind-address" "$mycnf"; then
        $SUDO sed -i 's/^\s*bind-address.*/bind-address = 0.0.0.0/' "$mycnf"
      else
        echo "bind-address = 0.0.0.0" | $SUDO tee -a "$mycnf" >/dev/null
      fi
      $SUDO systemctl restart mariadb
      ok "MariaDB écoute désormais sur 0.0.0.0:3306 ($mycnf)."
    else
      warn "Fichier de config MariaDB introuvable, bind-address non modifié."
    fi
  fi
}

# ----- 3. Déploiement des fichiers ------------------------------------------
deploy_app() {
  title "Copie des fichiers vers $INSTALL_DIR"

  $SUDO mkdir -p "$INSTALL_DIR"

  # rsync : copie tout sauf les fichiers de dev locaux et les logs existants.
  $SUDO rsync -a --delete \
      --exclude='.git/' \
      --exclude='.pid' \
      --exclude='start.sh' \
      --exclude='stop.sh' \
      --exclude='deploy.sh' \
      --exclude='logs/*.log' \
      "$SOURCE_DIR/" "$INSTALL_DIR/"

  # Permissions sécurisées : tout en lecture seule sauf logs/ en écriture.
  $SUDO chown -R "root:$PHP_GROUP" "$INSTALL_DIR"
  $SUDO find "$INSTALL_DIR" -type d -exec chmod 750 {} \;
  $SUDO find "$INSTALL_DIR" -type f -exec chmod 640 {} \;

  # logs/ : doit être writable par Apache.
  $SUDO mkdir -p "$INSTALL_DIR/logs"
  $SUDO chown -R "$PHP_USER:$PHP_GROUP" "$INSTALL_DIR/logs"
  $SUDO chmod 770 "$INSTALL_DIR/logs"

  # Génération de la configuration d'environnement Apache pour l'app.
  # Ces SetEnv sont lus par PHP via getenv() (config/config.php).
  $SUDO mkdir -p "$STATE_DIR"
  $SUDO tee "$STATE_DIR/apache-env.conf" >/dev/null <<EOF
# Variables d'environnement passées à PHP pour la médiathèque léger.
# Ce fichier est inclus depuis le vhost Apache.
SetEnv DB_HOST 127.0.0.1
SetEnv DB_NAME $DB_NAME
SetEnv DB_USER $DB_USER
SetEnv DB_PASS $DB_PASSWORD
EOF
  $SUDO chmod 640 "$STATE_DIR/apache-env.conf"
  $SUDO chown "root:$PHP_GROUP" "$STATE_DIR/apache-env.conf"

  save_state
  ok "Fichiers copiés et permissions appliquées."
  ok "Configuration env exportée : $STATE_DIR/apache-env.conf"
}

# ----- 4. Vhost Apache ------------------------------------------------------
setup_vhost() {
  title "Configuration du vhost Apache"

  local conf_dir conf_name apache_svc
  case "$DISTRO" in
    debian)
      conf_dir=/etc/apache2/sites-available
      conf_name="mediatheque-leger.conf"
      apache_svc=apache2
      ;;
    *)
      conf_dir=/etc/httpd/conf.d
      conf_name="mediatheque-leger.conf"
      apache_svc=httpd
      ;;
  esac

  # Vhost. La balise <FilesMatch> bloque l'accès direct aux fichiers
  # sensibles depuis l'extérieur (configs, scripts SQL, fichiers cachés).
  $SUDO tee "$conf_dir/$conf_name" >/dev/null <<APACHE
# Médiathèque léger — vhost généré le $(date '+%Y-%m-%d')
<VirtualHost *:80>
    ServerName $DOMAIN
    ServerAdmin $ADMIN_EMAIL

    DocumentRoot $INSTALL_DIR/public

    # Variables BD (lues par getenv() dans config/config.php)
    Include $STATE_DIR/apache-env.conf

    # --- Document root --------------------------------------------------
    <Directory $INSTALL_DIR/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # --- Refus explicite des dossiers internes -------------------------
    <Directory $INSTALL_DIR/app>
        Require all denied
    </Directory>
    <Directory $INSTALL_DIR/config>
        Require all denied
    </Directory>
    <Directory $INSTALL_DIR/logs>
        Require all denied
    </Directory>
    <Directory $INSTALL_DIR/sql>
        Require all denied
    </Directory>
    <Directory $INSTALL_DIR/docs>
        Require all denied
    </Directory>

    # Refus des fichiers cachés (.git, .env...) où qu'ils soient.
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    # --- Durcissement HTTP ---------------------------------------------
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "same-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    # HSTS uniquement après activation HTTPS (laisser commenté en HTTP pur) :
    # Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

    # --- Réglages PHP côté production ----------------------------------
    php_admin_flag display_errors    Off
    php_admin_value error_reporting  "E_ALL & ~E_DEPRECATED & ~E_NOTICE"
    php_admin_flag log_errors        On
    php_admin_flag expose_php        Off
    php_admin_value session.cookie_httponly 1
    php_admin_value session.cookie_samesite "Lax"

    ErrorLog \${APACHE_LOG_DIR}/mediatheque-leger-error.log
    CustomLog \${APACHE_LOG_DIR}/mediatheque-leger-access.log combined
</VirtualHost>
APACHE

  # Activation du vhost
  if [ "$DISTRO" = "debian" ]; then
    $SUDO a2ensite mediatheque-leger >/dev/null
    # Désactive le vhost par défaut s'il est encore actif (collision sur port 80).
    $SUDO a2dissite 000-default >/dev/null 2>&1 || true
    $SUDO a2enmod headers rewrite >/dev/null 2>&1 || true
  fi

  # Test de la conf avant rechargement
  if ! $SUDO apache2ctl configtest 2>&1 | grep -q "Syntax OK" \
     && ! $SUDO httpd -t 2>&1 | grep -q "Syntax OK"; then
    err "Erreur de syntaxe dans la conf Apache, rechargement annulé."
    $SUDO apache2ctl configtest 2>&1 || $SUDO httpd -t 2>&1 || true
    exit 1
  fi

  $SUDO systemctl reload "$apache_svc"
  ok "Vhost activé sur http://$DOMAIN/ ($conf_dir/$conf_name)."
}

# ----- 5. TLS via Let's Encrypt (optionnel) --------------------------------
setup_tls() {
  title "Activation HTTPS via Let's Encrypt"

  if [ "$DOMAIN" = "localhost" ] || [[ "$DOMAIN" == *.local ]] \
     || [[ "$DOMAIN" == *.lan ]]; then
    warn "Domaine non public ($DOMAIN) — Let's Encrypt ignoré."
    return 0
  fi

  if [ -z "$ADMIN_EMAIL" ] || [ "$ADMIN_EMAIL" = "webmaster@$DOMAIN" ]; then
    warn "Définissez ADMIN_EMAIL=vous@example.fr pour activer HTTPS."
    return 1
  fi

  case "$DISTRO" in
    debian) $SUDO apt-get install -y --no-install-recommends certbot python3-certbot-apache ;;
    rhel)   $SUDO dnf install -y certbot python3-certbot-apache ;;
    *) err "Certbot non géré automatiquement sur $DISTRO."; return 1 ;;
  esac

  $SUDO certbot --apache -n --agree-tos -m "$ADMIN_EMAIL" -d "$DOMAIN" --redirect

  # Active HSTS désormais qu'on est sûr d'être en HTTPS
  warn "Pensez à dé-commenter la ligne Strict-Transport-Security du vhost."
  ok "HTTPS activé. Renouvellement auto via le timer systemd 'certbot.timer'."
}

# ----- 6. Vérification ------------------------------------------------------
verify() {
  title "Vérification du déploiement"
  local apache_svc=apache2
  [ "$DISTRO" = "debian" ] || apache_svc=httpd

  if $SUDO systemctl is-active --quiet "$apache_svc"; then
    ok "$apache_svc actif"
  else
    err "$apache_svc inactif"
  fi

  if $SUDO systemctl is-active --quiet mariadb; then
    ok "mariadb actif"
  else
    err "mariadb inactif"
  fi

  # Test PHP + BD
  local php_test
  php_test=$($SUDO -u "$PHP_USER" php -r "
    try {
      \$p = new PDO('mysql:host=127.0.0.1;dbname=$DB_NAME;charset=utf8mb4',
                   '$DB_USER', '$DB_PASSWORD',
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
      \$n = \$p->query('SELECT COUNT(*) FROM agent')->fetchColumn();
      echo 'OK:' . \$n . ' agents';
    } catch (Exception \$e) {
      echo 'ERR:' . \$e->getMessage();
    }
  " 2>&1)
  if [[ "$php_test" == OK:* ]]; then
    ok "Connexion PHP→BD : $php_test"
  else
    err "Connexion PHP→BD : $php_test"
  fi

  # Test HTTP
  local code
  code=$(curl -s -o /dev/null -w "%{http_code}" -L "http://localhost/" || echo 000)
  if [[ "$code" =~ ^(200|302)$ ]]; then
    ok "HTTP localhost : $code"
  else
    err "HTTP localhost : $code (consultez /var/log/apache2/mediatheque-leger-error.log)"
  fi
}

# ----- 7. Résumé ------------------------------------------------------------
summary() {
  cat <<EOF

${C_OK}═══════════════════════════════════════════════════════════════${C_RESET}
${C_OK}  Déploiement terminé${C_RESET}
${C_OK}═══════════════════════════════════════════════════════════════${C_RESET}

  Domaine        : $DOMAIN
  Répertoire     : $INSTALL_DIR
  Base / user    : $DB_NAME / $DB_USER
  Mot de passe   : $DB_PASSWORD
  Config env     : $STATE_DIR/apache-env.conf
  État conservé  : $STATE_FILE  (root, 600)

  URL :   ${C_INFO}http://$DOMAIN/${C_RESET}

  Comptes de démonstration (à CHANGER en production) :
    admin@mediatheque.fr / admin123
    agent@mediatheque.fr / agent123

  Logs utiles :
    sudo tail -f /var/log/apache2/mediatheque-leger-error.log
    sudo tail -f $INSTALL_DIR/logs/app_\$(date +%Y-%m).log

  Pour activer HTTPS (domaine public + DNS configurés) :
    sudo ADMIN_EMAIL=$ADMIN_EMAIL $0 tls

  Pour autoriser le client lourd à se connecter à distance à la BD :
    sudo DB_REMOTE_ACCESS=yes $0 database

EOF
}

# ----- 8. Status (état actuel du déploiement) ------------------------------
status() {
  title "État du déploiement"
  [ -r "$STATE_FILE" ] \
    && ok "État conservé : $STATE_FILE" \
    || warn "Aucun déploiement précédent enregistré ($STATE_FILE absent)."

  [ -d "$INSTALL_DIR" ] \
    && ok "Répertoire d'installation : $INSTALL_DIR" \
    || err "Répertoire manquant : $INSTALL_DIR"

  local apache_svc=apache2
  [ "$DISTRO" = "debian" ] || apache_svc=httpd
  $SUDO systemctl is-active --quiet "$apache_svc" \
    && ok "$apache_svc actif" \
    || err "$apache_svc inactif"
  $SUDO systemctl is-active --quiet mariadb \
    && ok "mariadb actif" \
    || err "mariadb inactif"
}

# =============================================================================
#                              DISPATCHER
# =============================================================================

aide() {
  cat <<EOF
Médiathèque léger — script de déploiement serveur

Usage : sudo $(basename "$0") [commande]

Commandes :
  all        Déploiement complet (défaut) : prereqs + database + app + vhost + verify
  prereqs    Installe Apache, PHP, MariaDB et les extensions PHP
  database   Crée la base + l'utilisateur dédié, charge le schéma
  app        Copie les fichiers dans INSTALL_DIR avec les permissions de prod
  vhost      Génère et active le vhost Apache (durci, sans exposition de app/config)
  tls        Active HTTPS via certbot Let's Encrypt (domaine public requis)
  verify     Teste Apache, MariaDB, la connexion PHP→BD et l'URL locale
  status     Affiche l'état du déploiement
  help       Cette aide

Variables surchageables (passez-les avant la commande ou exportez-les) :
  INSTALL_DIR      Répertoire d'installation       (def. /var/www/mediatheque-leger)
  DOMAIN           Nom de domaine du vhost         (def. hostname système)
  DB_NAME          Nom de la base                  (def. mediatheque)
  DB_USER          Utilisateur BD                  (def. mediatheque)
  DB_PASSWORD      Mot de passe                    (généré aléatoirement par défaut)
  ADMIN_EMAIL      Email contact (pour TLS)        (def. webmaster@<domain>)
  DB_REMOTE_ACCESS Autoriser une connexion BD distante (yes/no, def. no)
  PHP_USER         Utilisateur Apache              (def. www-data/apache)

Exemples :
  # Déploiement le plus simple (sudo seul) :
  sudo ./deploy.sh

  # Avec domaine + email pour HTTPS :
  sudo DOMAIN=mediatheque.example.fr ADMIN_EMAIL=admin@example.fr \\
       ./deploy.sh all
  sudo DOMAIN=mediatheque.example.fr ADMIN_EMAIL=admin@example.fr \\
       ./deploy.sh tls

  # Pour autoriser le client lourd Java à se connecter à la BD du serveur :
  sudo DB_REMOTE_ACCESS=yes ./deploy.sh database

  # Re-déploiement (mise à jour de l'application sans toucher à la BD) :
  sudo ./deploy.sh app && sudo systemctl reload apache2
EOF
}

cmd="${1:-all}"

case "$cmd" in
  prereqs)  install_prereqs ;;
  database) setup_database ; save_state ;;
  app)      deploy_app ;;
  vhost)    setup_vhost ;;
  tls)      setup_tls ;;
  verify)   verify ;;
  status)   status ;;
  all)
    install_prereqs
    setup_database
    save_state
    deploy_app
    setup_vhost
    verify
    summary
    ;;
  help|-h|--help)
    aide
    ;;
  *)
    err "Commande inconnue : $cmd"
    aide
    exit 1
    ;;
esac
