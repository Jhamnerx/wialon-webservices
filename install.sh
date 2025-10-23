#!/bin/bash

################################################################################
# Script de Instalación Automática - Wialon WebServices
# Sistema Operativo: AlmaLinux 9
# Servicios: PHP 8.3, MariaDB, Redis, Apache (httpd), Supervisor, Node.js, Composer
################################################################################

set -e  # Salir si hay algún error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables de configuración
APP_USER="root"
DB_NAME="wialon_webservices"
DB_USER="root"
DB_PASSWORD=""  # Contraseña de root de MariaDB
DOMAIN="your-domain.com"  # Cambiar según necesidad
GIT_REPO="https://github.com/Jhamnerx/wialon-webservices"  # URL del repositorio de GitHub

# Función para imprimir mensajes
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Verificar que se ejecuta como root
if [[ $EUID -ne 0 ]]; then
   print_error "Este script debe ejecutarse como root (sudo)"
   exit 1
fi

print_step "Iniciando instalación de Wialon WebServices en AlmaLinux 9..."

# Extraer nombre del repositorio de la URL de GitHub
APP_NAME=$(basename ${GIT_REPO} .git)
APP_DIR="/var/www/${APP_NAME}"

print_message "Repositorio: ${GIT_REPO}"
print_message "Nombre de la aplicación: ${APP_NAME}"
print_message "Directorio de instalación: ${APP_DIR}"

# Solicitar confirmación o cambio de dominio
read -p "Ingrese el dominio para la aplicación [${DOMAIN}]: " input_domain
if [ ! -z "$input_domain" ]; then
    DOMAIN=$input_domain
fi

print_message "Dominio configurado: ${DOMAIN}"
echo ""

################################################################################
# 1. ACTUALIZAR SISTEMA E INSTALAR HERRAMIENTAS BÁSICAS
################################################################################
print_step "Actualizando sistema operativo..."
dnf update -y
dnf install -y epel-release
dnf config-manager --set-enabled crb

print_step "Instalando herramientas básicas..."
dnf install -y nano wget curl

################################################################################
# 2. INSTALAR PHP 8.3
################################################################################
print_step "Instalando PHP 8.3 y extensiones..."
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
dnf module reset php -y
dnf module enable php:remi-8.3 -y

dnf install -y \
    php \
    php-cli \
    php-fpm \
    php-mysqlnd \
    php-pdo \
    php-mbstring \
    php-xml \
    php-bcmath \
    php-json \
    php-zip \
    php-gd \
    php-curl \
    php-intl \
    php-opcache \
    php-redis \
    php-soap \
    php-fileinfo \
    php-tokenizer \
    php-dom \
    php-simplexml

# Verificar instalación de PHP
php -v

################################################################################
# 3. INSTALAR MARIADB 10.11
################################################################################
print_step "Instalando MariaDB 10.11..."
dnf install -y mariadb-server mariadb

# Iniciar y habilitar MariaDB
systemctl start mariadb
systemctl enable mariadb

# Crear base de datos
print_message "Creando base de datos..."
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Asegurar instalación de MariaDB
print_step "Configurando seguridad de MariaDB..."
print_warning "A continuación se ejecutará mysql_secure_installation"
print_warning "Por favor, configure una contraseña segura para root y guárdela para usarla en el archivo .env"
echo ""
mysql_secure_installation

print_message "Seguridad de MariaDB configurada"
print_warning "Recuerde guardar la contraseña de root de MariaDB para configurar el archivo .env"

################################################################################
# 4. INSTALAR REDIS
################################################################################
print_step "Instalando Redis..."
dnf install -y redis

# Configurar Redis
systemctl start redis
systemctl enable redis

# Configuración básica de Redis
cat > /etc/redis/redis.conf.d/laravel.conf <<EOF
maxmemory 256mb
maxmemory-policy allkeys-lru
EOF

systemctl restart redis

################################################################################
# 5. INSTALAR APACHE (HTTPD)
################################################################################
print_step "Instalando Apache (httpd)..."
dnf install -y httpd mod_ssl

# Habilitar y arrancar Apache
systemctl enable httpd

################################################################################
# 6. INSTALAR NODE.JS 22 LTS
################################################################################
print_step "Instalando Node.js 22 LTS..."
dnf module reset nodejs -y
dnf module enable nodejs:22 -y
dnf install -y nodejs npm

# Verificar instalación
node -v
npm -v

################################################################################
# 7. INSTALAR COMPOSER
################################################################################
print_step "Instalando Composer..."
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Verificar instalación
composer --version

################################################################################
# 8. INSTALAR SUPERVISOR
################################################################################
print_step "Instalando Supervisor..."
dnf install -y supervisor

# Crear directorio de configuración si no existe
mkdir -p /etc/supervisor/conf.d

# Habilitar y arrancar Supervisor
systemctl enable supervisord
systemctl start supervisord

################################################################################
# 9. INSTALAR GIT
################################################################################
print_step "Instalando Git..."
dnf install -y git

################################################################################
# 10. CLONAR REPOSITORIO DE GITHUB
################################################################################
print_step "Clonando repositorio desde GitHub..."
if [ -d "${APP_DIR}" ]; then
    print_warning "El directorio ${APP_DIR} ya existe. Eliminando..."
    rm -rf ${APP_DIR}
fi

git clone ${GIT_REPO} ${APP_DIR}

if [ ! -d "${APP_DIR}" ]; then
    print_error "Error al clonar el repositorio"
    exit 1
fi

print_message "Repositorio clonado exitosamente"

# Crear directorios necesarios si no existen
mkdir -p ${APP_DIR}/storage/logs
mkdir -p ${APP_DIR}/storage/framework/{sessions,views,cache}
mkdir -p ${APP_DIR}/bootstrap/cache

################################################################################
# 11. CONFIGURAR ARCHIVO .ENV
################################################################################
print_step "Configurando archivo .env..."
cd ${APP_DIR}
if [ ! -f "${APP_DIR}/.env" ]; then
    cp ${APP_DIR}/.env.example ${APP_DIR}/.env
    
    # Solicitar contraseña de root de MariaDB
    echo ""
    read -sp "Ingrese la contraseña de root de MariaDB configurada anteriormente: " DB_PASSWORD
    echo ""
    
    # Actualizar valores en .env
    sed -i "s|APP_URL=.*|APP_URL=http://${DOMAIN}|g" ${APP_DIR}/.env
    sed -i "s|DB_DATABASE=.*|DB_DATABASE=${DB_NAME}|g" ${APP_DIR}/.env
    sed -i "s|DB_USERNAME=.*|DB_USERNAME=${DB_USER}|g" ${APP_DIR}/.env
    sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD}|g" ${APP_DIR}/.env
    
    # Guardar credenciales
    echo "DB_NAME=${DB_NAME}" > /root/.wialon_db_credentials
    echo "DB_USER=${DB_USER}" >> /root/.wialon_db_credentials
    echo "DB_PASSWORD=${DB_PASSWORD}" >> /root/.wialon_db_credentials
    chmod 600 /root/.wialon_db_credentials
    
    print_message "Archivo .env configurado"
    print_message "Credenciales guardadas en /root/.wialon_db_credentials"
else
    print_warning "El archivo .env ya existe, no se modificará"
fi

################################################################################
# 12. INSTALAR DEPENDENCIAS DE LA APLICACIÓN
################################################################################
print_step "Instalando dependencias de Composer..."
cd ${APP_DIR}
composer install --no-dev --optimize-autoloader --no-interaction

print_step "Instalando dependencias de Node.js..."
npm install

print_step "Compilando assets..."
npm run build

################################################################################
# 13. CONFIGURAR PERMISOS
################################################################################
print_step "Configurando permisos..."
chown -R root:apache ${APP_DIR}
chmod -R 755 ${APP_DIR}
chmod -R 775 ${APP_DIR}/storage
chmod -R 775 ${APP_DIR}/bootstrap/cache
chgrp -R apache ${APP_DIR}/storage ${APP_DIR}/bootstrap/cache

################################################################################
# 14. CONFIGURAR PHP-FPM
################################################################################
print_step "Configurando PHP-FPM..."
cat > /etc/php-fpm.d/${APP_NAME}.conf <<EOF
[${APP_NAME}]
user = root
group = apache
listen = /run/php-fpm/${APP_NAME}.sock
listen.owner = apache
listen.group = apache
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[error_log] = ${APP_DIR}/storage/logs/php-fpm.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path] = ${APP_DIR}/storage/framework/sessions
EOF

systemctl enable php-fpm
systemctl restart php-fpm

################################################################################
# 15. CONFIGURAR APACHE VIRTUALHOST
################################################################################
print_step "Configurando VirtualHost de Apache..."
cat > /etc/httpd/conf.d/${APP_NAME}.conf <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot ${APP_DIR}/public

    <Directory ${APP_DIR}/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php-fpm/${APP_NAME}.sock|fcgi://localhost"
    </FilesMatch>

    ErrorLog ${APP_DIR}/storage/logs/apache-error.log
    CustomLog ${APP_DIR}/storage/logs/apache-access.log combined

    # Compresión
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/plain
        AddOutputFilterByType DEFLATE text/html
        AddOutputFilterByType DEFLATE text/xml
        AddOutputFilterByType DEFLATE text/css
        AddOutputFilterByType DEFLATE application/xml
        AddOutputFilterByType DEFLATE application/xhtml+xml
        AddOutputFilterByType DEFLATE application/rss+xml
        AddOutputFilterByType DEFLATE application/javascript
        AddOutputFilterByType DEFLATE application/x-javascript
    </IfModule>
</VirtualHost>
EOF

# El módulo proxy_fcgi viene incluido en httpd
systemctl restart httpd

################################################################################
# 16. GENERAR CLAVE DE APLICACIÓN Y EJECUTAR MIGRACIONES
################################################################################
print_step "Generando clave de aplicación..."
cd ${APP_DIR}
php artisan key:generate --force

print_step "Ejecutando migraciones de base de datos..."
php artisan migrate --force

print_step "Optimizando aplicación para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

################################################################################
# 17. DESHABILITAR SELINUX
################################################################################
print_step "Deshabilitando SELinux..."
setenforce 0
sed -i 's/^SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config
print_message "SELinux deshabilitado (requiere reinicio para aplicar cambios permanentes)"

################################################################################
# 18. CONFIGURAR SUPERVISOR PARA COLAS LARAVEL
################################################################################
print_step "Configurando Supervisor para colas Laravel..."

# Worker principal (web-service)
cat > /etc/supervisor/conf.d/${APP_NAME}-web-service.conf <<EOF
[program:${APP_NAME}-web-service]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=web-service --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=4
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker-web-service.log
stopwaitsecs=3600
startsecs=0
EOF

# Worker para SISCOP
cat > /etc/supervisor/conf.d/${APP_NAME}-siscop.conf <<EOF
[program:${APP_NAME}-siscop]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=siscop-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=3
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker-siscop.log
stopwaitsecs=3600
startsecs=0
EOF

# Worker para OSINERGMIN
cat > /etc/supervisor/conf.d/${APP_NAME}-osinergmin.conf <<EOF
[program:${APP_NAME}-osinergmin]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=osinergmin-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=3
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker-osinergmin.log
stopwaitsecs=3600
startsecs=0
EOF

# Worker para SUTRAN
cat > /etc/supervisor/conf.d/${APP_NAME}-sutran.conf <<EOF
[program:${APP_NAME}-sutran]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=sutran-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=3
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker-sutran.log
stopwaitsecs=3600
startsecs=0
EOF

# Worker para reenvío histórico
cat > /etc/supervisor/conf.d/${APP_NAME}-reenvio.conf <<EOF
[program:${APP_NAME}-reenvio]
process_name=%(program_name)s_%(process_num)02d
command=php ${APP_DIR}/artisan queue:work redis --queue=reenviar-historial --sleep=3 --tries=1 --max-time=7200 --timeout=7200
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=${APP_USER}
numprocs=2
redirect_stderr=true
stdout_logfile=${APP_DIR}/storage/logs/worker-reenvio.log
stopwaitsecs=7200
startsecs=0
EOF

# Recargar configuración de Supervisor
supervisorctl reread
supervisorctl update

################################################################################
# 19. CONFIGURAR CRON PARA LARAVEL SCHEDULER Y LIMPIEZA
################################################################################
print_step "Configurando tareas programadas (cron)..."

# Agregar tareas al crontab de root
(crontab -l 2>/dev/null; echo "# Laravel Scheduler - Wialon WebServices") | crontab -
(crontab -l 2>/dev/null; echo "* * * * * cd ${APP_DIR} && php artisan schedule:run >> /dev/null 2>&1") | crontab -
(crontab -l 2>/dev/null; echo "") | crontab -
(crontab -l 2>/dev/null; echo "# Limpiar colas cada 12 horas") | crontab -
(crontab -l 2>/dev/null; echo "0 0,12 * * * cd ${APP_DIR} && php artisan queue:flush >> /dev/null 2>&1") | crontab -
(crontab -l 2>/dev/null; echo "") | crontab -
(crontab -l 2>/dev/null; echo "# Limpiar logs antiguos diariamente") | crontab -
(crontab -l 2>/dev/null; echo "0 2 * * * cd ${APP_DIR} && php artisan app:clear-logs 7 >> /dev/null 2>&1") | crontab -

print_message "Tareas cron configuradas para usuario root"

################################################################################
# 20. RESUMEN DE INSTALACIÓN
################################################################################
print_step "Instalación completada!"
echo ""
echo "=========================================="
echo "  RESUMEN DE INSTALACIÓN"
echo "=========================================="
echo ""
echo "✓ PHP 8.3 instalado y configurado"
echo "✓ MariaDB instalado y base de datos creada"
echo "✓ Redis instalado y en ejecución"
echo "✓ Apache (httpd) configurado"
echo "✓ Node.js y npm instalados"
echo "✓ Composer instalado"
echo "✓ Supervisor configurado con 5 workers:"
echo "  - web-service (4 procesos)"
echo "  - siscop-queue (3 procesos)"
echo "  - osinergmin-queue (3 procesos)"
echo "  - sutran-queue (3 procesos)"
echo "  - reenviar-historial (2 procesos)"
echo "✓ Cron configurado para scheduler y limpieza"
echo ""
echo "=========================================="
echo "  INFORMACIÓN IMPORTANTE"
echo "=========================================="
echo ""
echo "✓ Aplicación clonada desde: ${GIT_REPO}"
echo "✓ Dependencias instaladas"
echo "✓ Base de datos migrada"
echo "✓ Aplicación optimizada"
echo "✓ Workers iniciados"
echo ""
echo "Dominio configurado: http://${DOMAIN}"
echo "Directorio de la aplicación: ${APP_DIR}"
echo ""
echo "Para acceder a la aplicación, asegúrate de:"
echo "1. Configurar el DNS del dominio ${DOMAIN} apuntando a este servidor"
echo "2. O agregar una entrada en /etc/hosts para pruebas locales"
echo ""

echo "=========================================="
echo "  CREDENCIALES"
echo "=========================================="
echo ""
echo "Base de datos: ${DB_NAME}"
echo "Usuario DB: ${DB_USER} (root de MariaDB)"
echo "Contraseña DB: Ver /root/.wialon_db_credentials"
echo ""
echo "Usuario aplicación: root"
echo "Directorio aplicación: ${APP_DIR}"
echo "Repositorio GitHub: ${GIT_REPO}"
echo ""
echo "IMPORTANTE: SELinux ha sido deshabilitado."
echo "Se recomienda reiniciar el servidor para aplicar todos los cambios."
echo ""
echo "=========================================="
echo "  COMANDOS ÚTILES"
echo "=========================================="
echo ""
echo "Ver estado de workers:"
echo "  supervisorctl status"
echo ""
echo "Reiniciar workers:"
echo "  supervisorctl restart all"
echo ""
echo "Ver logs de workers:"
echo "  tail -f ${APP_DIR}/storage/logs/worker-*.log"
echo ""
echo "Limpiar cache de Laravel:"
echo "  php artisan cache:clear"
echo "  php artisan config:clear"
echo "  php artisan route:clear"
echo "  php artisan view:clear"
echo ""
echo "Monitorear colas:"
echo "  php artisan queue:monitor redis:web-service,siscop-queue,osinergmin-queue,sutran-queue,reenviar-historial"
echo ""
print_message "Instalación finalizada con éxito!"
