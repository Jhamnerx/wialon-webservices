# Guía de Instalación Manual - Wialon WebServices

## AlmaLinux 9

Esta guía detalla los pasos para instalar manualmente el sistema Wialon WebServices en un servidor AlmaLinux 9.

**Repositorio GitHub:** https://github.com/Jhamnerx/wialon-webservices

---

## 📋 Requisitos Previos

-   Servidor AlmaLinux 9 con acceso root
-   Mínimo 2GB RAM
-   20GB espacio en disco
-   Conexión a internet
-   Acceso SSH
-   Acceso al repositorio de GitHub

---

## 🚀 Proceso de Instalación

### 1. Actualizar Sistema e Instalar Herramientas Básicas

```bash
sudo dnf update -y
sudo dnf install -y epel-release
sudo dnf config-manager --set-enabled crb

# Instalar herramientas básicas
sudo dnf install -y nano wget curl
```

---

### 2. Instalar PHP 8.3

#### 2.1 Agregar Repositorio Remi

```bash
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm
sudo dnf module reset php -y
sudo dnf module enable php:remi-8.3 -y
```

#### 2.2 Instalar PHP y Extensiones

```bash
sudo dnf install -y \
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
```

#### 2.3 Verificar Instalación

```bash
php -v
# Debe mostrar: PHP 8.3.x
```

---

### 3. Instalar MariaDB 10.11

#### 3.1 Instalar MariaDB

```bash
sudo dnf install -y mariadb-server mariadb
```

#### 3.2 Iniciar y Habilitar MariaDB

```bash
sudo systemctl start mariadb
sudo systemctl enable mariadb
```

#### 3.3 Crear Base de Datos

```bash
# Crear base de datos (sin contraseña aún)
sudo mysql -e "CREATE DATABASE wialon_webservices CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

#### 3.4 Configurar Seguridad de MariaDB

```bash
sudo mysql_secure_installation
```

**Responder a las preguntas:**

-   **Enter current password for root:** (Presionar Enter, no hay contraseña aún)
-   **Switch to unix_socket authentication:** N
-   **Change the root password:** Y (establecer contraseña segura y **guardarla**)
-   **Remove anonymous users:** Y
-   **Disallow root login remotely:** Y
-   **Remove test database:** Y
-   **Reload privilege tables:** Y

**⚠️ IMPORTANTE:** Guardar la contraseña de root de MariaDB, se necesitará para el archivo `.env`

**Nota:** Usaremos el usuario `root` de MariaDB para la aplicación.

---

### 4. Instalar Redis

```bash
sudo dnf install -y redis

# Configurar Redis para Laravel
sudo tee /etc/redis/redis.conf.d/laravel.conf > /dev/null <<EOF
maxmemory 256mb
maxmemory-policy allkeys-lru
EOF

# Iniciar y habilitar Redis
sudo systemctl start redis
sudo systemctl enable redis

# Verificar estado
sudo systemctl status redis
```

---

### 5. Instalar Apache (httpd)

```bash
sudo dnf install -y httpd mod_ssl

# Habilitar Apache
sudo systemctl enable httpd
```

**Nota:** No iniciar Apache aún, se configurará más adelante.

---

### 6. Instalar Node.js 22 LTS

```bash
sudo dnf module reset nodejs -y
sudo dnf module enable nodejs:22 -y
sudo dnf install -y nodejs npm

# Verificar instalación
node -v  # Debe mostrar v22.x.x
npm -v
```

---

### 7. Instalar Composer

```bash
# Descargar instalador
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

# Instalar globalmente
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Limpiar
php -r "unlink('composer-setup.php');"

# Verificar
composer --version
```

---

### 8. Instalar Supervisor

```bash
sudo dnf install -y supervisor

# Crear directorio de configuración
sudo mkdir -p /etc/supervisor/conf.d

# Habilitar y arrancar Supervisor
sudo systemctl enable supervisord
sudo systemctl start supervisord
```

---

### 9. Instalar Git

```bash
sudo dnf install -y git

# Verificar instalación
git --version
```

---

### 10. Clonar Repositorio desde GitHub

```bash
# Clonar el repositorio
cd /var/www
sudo git clone https://github.com/Jhamnerx/wialon-webservices

# Verificar que se haya clonado correctamente
ls -la /var/www/wialon-webservices
```

**Nota:** El nombre del directorio será `wialon-webservices` (extraído del nombre del repositorio en GitHub).

---

### 11. Crear Estructura de Directorios Adicionales

```bash
# Crear directorios necesarios si no existen en el repositorio
sudo mkdir -p /var/www/wialon-webservices/storage/logs
sudo mkdir -p /var/www/wialon-webservices/storage/framework/{sessions,views,cache}
sudo mkdir -p /var/www/wialon-webservices/bootstrap/cache
```

---

### 12. Configurar Archivo .env

```bash
# Copiar archivo de ejemplo
sudo cp /var/www/wialon-webservices/.env.example /var/www/wialon-webservices/.env

# Editar archivo .env
sudo nano /var/www/wialon-webservices/.env
```

**Configuración mínima requerida:**

```env
APP_NAME="Wialon WebServices"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=America/Lima
APP_URL=http://tu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wialon_webservices
DB_USERNAME=root
DB_PASSWORD=TU_CONTRASEÑA_SEGURA

QUEUE_CONNECTION=redis

SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

CACHE_STORE=redis
CACHE_PREFIX=wialon_cache
```

**Guardar y cerrar:** `Ctrl+X`, luego `Y`, luego `Enter`

---

### 13. Instalar Dependencias de la Aplicación

```bash
# Cambiar al directorio de la aplicación
cd /var/www/wialon-webservices

# Instalar dependencias de Composer
sudo composer install --no-dev --optimize-autoloader --no-interaction

# Instalar dependencias de Node.js
sudo npm install

# Compilar assets
sudo npm run build
```

---

### 14. Configurar Permisos

```bash
# Establecer propietario root con grupo apache
sudo chown -R root:apache /var/www/wialon-webservices

# Permisos generales
sudo chmod -R 755 /var/www/wialon-webservices
chmod -R 777 vendor/
chmod 777 storage/* -R

# Permisos de escritura para storage y cache
sudo chmod -R 775 /var/www/wialon-webservices/storage
sudo chmod -R 775 /var/www/wialon-webservices/bootstrap/cache

# Asegurar que apache tenga permisos de grupo
sudo chgrp -R apache /var/www/wialon-webservices/storage
sudo chgrp -R apache /var/www/wialon-webservices/bootstrap/cache
```

---

### 15. Generar Clave de Aplicación

```bash
cd /var/www/wialon-webservices
sudo php artisan key:generate --force
```

---

### 16. Ejecutar Migraciones y Seeders

#### 16.1 Ejecutar Migraciones

```bash
cd /var/www/wialon-webservices
sudo php artisan migrate --force
```

#### 16.2 Ejecutar Seeders (Opcional)

Los seeders poblarán la base de datos con datos iniciales necesarios para el funcionamiento del sistema.

````bash
cd /var/www/wialon-webservices

# Ejecutar todos los seeders
sudo php artisan db:seed --force


**Nota:** Los seeders crearán:

-   Roles y permisos iniciales del sistema
-   Servicios predefinidos (SISCOP, OSINERGMIN, SUTRAN)
-   Usuario administrador por defecto (si aplica)
-   Configuraciones iniciales necesarias

**⚠️ IMPORTANTE:** Si ya tienes datos en producción, **NO** ejecutes los seeders ya que podrían sobrescribir información existente. Solo ejecuta los seeders en instalaciones nuevas.

---

### 17. Configurar PHP-FPM

```bash
# Crear configuración de pool
sudo tee /etc/php-fpm.d/wialon-webservices.conf > /dev/null <<'EOF'
[wialon-webservices]
user = root
group = apache
listen = /run/php-fpm/wialon-webservices.sock
listen.owner = apache
listen.group = apache
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

php_admin_value[error_log] = /var/www/wialon-webservices/storage/logs/php-fpm.log
php_admin_flag[log_errors] = on
php_value[session.save_handler] = files
php_value[session.save_path] = /var/www/wialon-webservices/storage/framework/sessions
EOF

# Habilitar y reiniciar PHP-FPM
sudo systemctl enable php-fpm
sudo systemctl restart php-fpm
````

---

### 18. Configurar VirtualHost de Apache

```bash
# Crear configuración de VirtualHost
sudo tee /etc/httpd/conf.d/wialon-webservices.conf > /dev/null <<'EOF'
<VirtualHost *:80>
    ServerName tu-dominio.com
    ServerAlias www.tu-dominio.com
    DocumentRoot /var/www/wialon-webservices/public

    <Directory /var/www/wialon-webservices/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
    </Directory>

    ErrorLog /var/www/wialon-webservices/storage/logs/apache-error.log
    CustomLog /var/www/wialon-webservices/storage/logs/apache-access.log combined

</VirtualHost>
EOF

# Reemplazar "tu-dominio.com" con tu dominio real
sudo sed -i 's/tu-dominio.com/TU_DOMINIO_REAL/g' /etc/httpd/conf.d/wialon-webservices.conf
```

---

### 19. Configurar Supervisor para Colas

#### 19.1 Worker Web Service (Principal)

```bash
sudo tee /etc/supervisor/conf.d/wialon-webservices-web-service.conf > /dev/null <<'EOF'
[program:wialon-webservices-web-service]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wialon-webservices/artisan queue:work redis --queue=web-service --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/wialon-webservices/storage/logs/worker-web-service.log
stopwaitsecs=3600
startsecs=0
EOF
```

#### 19.2 Worker SISCOP

```bash
sudo tee /etc/supervisor/conf.d/wialon-webservices-siscop.conf > /dev/null <<'EOF'
[program:wialon-webservices-siscop]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wialon-webservices/artisan queue:work redis --queue=siscop-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/wialon-webservices/storage/logs/worker-siscop.log
stopwaitsecs=3600
startsecs=0
EOF
```

#### 19.3 Worker OSINERGMIN

```bash
sudo tee /etc/supervisor/conf.d/wialon-webservices-osinergmin.conf > /dev/null <<'EOF'
[program:wialon-webservices-osinergmin]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wialon-webservices/artisan queue:work redis --queue=osinergmin-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/wialon-webservices/storage/logs/worker-osinergmin.log
stopwaitsecs=3600
startsecs=0
EOF
```

#### 19.4 Worker SUTRAN

```bash
sudo tee /etc/supervisor/conf.d/wialon-webservices-sutran.conf > /dev/null <<'EOF'
[program:wialon-webservices-sutran]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wialon-webservices/artisan queue:work redis --queue=sutran-queue --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=3
redirect_stderr=true
stdout_logfile=/var/www/wialon-webservices/storage/logs/worker-sutran.log
stopwaitsecs=3600
startsecs=0
EOF
```

#### 19.5 Worker Reenvío Histórico

```bash
sudo tee /etc/supervisor/conf.d/wialon-webservices-reenvio.conf > /dev/null <<'EOF'
[program:wialon-webservices-reenvio]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/wialon-webservices/artisan queue:work redis --queue=reenviar-historial --sleep=3 --tries=1 --max-time=7200 --timeout=7200
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/wialon-webservices/storage/logs/worker-reenvio.log
stopwaitsecs=7200
startsecs=0
EOF
```

#### 19.6 Recargar Configuración de Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

### 20. Configurar Tareas Programadas (Cron)

```bash
# Editar crontab de root
export EDITOR=/bin/nano
sudo crontab -e

# Agregar las siguientes líneas:
# Laravel Scheduler - Se ejecuta cada minuto
* * * * * cd /var/www/wialon-webservices && php artisan schedule:run >> /dev/null 2>&1

# Limpiar colas fallidas cada 12 horas (a las 00:00 y 12:00)
0 0,12 * * * cd /var/www/wialon-webservices && php artisan queue:flush >> /dev/null 2>&1

# Limpiar logs de base de datos mayores a 7 días (cada día a las 02:00)
0 2 * * * cd /var/www/wialon-webservices && php artisan app:clear-logs 7 >> /dev/null 2>&1
```

**O usar este comando para agregar todo de una vez:**

```bash
(sudo crontab -l 2>/dev/null; echo "# Laravel Scheduler - Wialon WebServices") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "* * * * * cd /var/www/wialon-webservices && php artisan schedule:run >> /dev/null 2>&1") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "# Limpiar colas cada 12 horas") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "0 0,12 * * * cd /var/www/wialon-webservices && php artisan queue:flush >> /dev/null 2>&1") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "# Limpiar logs antiguos diariamente") | sudo crontab -
(sudo crontab -l 2>/dev/null; echo "0 2 * * * cd /var/www/wialon-webservices && php artisan app:clear-logs 7 >> /dev/null 2>&1") | sudo crontab -

# Verificar instalación
sudo crontab -l
```

---

### 21. Optimizar Laravel para Producción

```bash
cd /var/www/wialon-webservices

# Optimizar autoloader de Composer
sudo composer dump-autoload --optimize

# Cachear configuración
sudo php artisan config:cache

# Cachear rutas
sudo php artisan route:cache

# Cachear vistas
sudo php artisan view:cache

# Cachear eventos
sudo php artisan event:cache
```

---

### 22. Deshabilitar SELinux

```bash
# Deshabilitar SELinux temporalmente
sudo setenforce 0

# Deshabilitar SELinux permanentemente
sudo sed -i 's/^SELINUX=enforcing/SELINUX=disabled/' /etc/selinux/config

# Verificar estado
getenforce  # Debe mostrar: Permissive
```

**Nota:** SELinux quedará completamente deshabilitado después del reinicio del servidor.

---

### 23. Iniciar Servicios

```bash
# Iniciar Apache
sudo systemctl start httpd

# Verificar estado de todos los servicios
sudo systemctl status httpd
sudo systemctl status php-fpm
sudo systemctl status mariadb
sudo systemctl status redis
sudo systemctl status supervisord

# Ver estado de workers
sudo supervisorctl status
```

---

## 📊 Verificación de Instalación

### Verificar Aplicación Web

```bash
# Probar acceso local
curl -I http://localhost
```

### Verificar Workers de Supervisor

```bash
sudo supervisorctl status
```

**Salida esperada:**

```
wialon-webservices-web-service:wialon-webservices-web-service_00   RUNNING
wialon-webservices-web-service:wialon-webservices-web-service_01   RUNNING
wialon-webservices-web-service:wialon-webservices-web-service_02   RUNNING
wialon-webservices-web-service:wialon-webservices-web-service_03   RUNNING
wialon-webservices-siscop:wialon-webservices-siscop_00              RUNNING
wialon-webservices-siscop:wialon-webservices-siscop_01              RUNNING
wialon-webservices-siscop:wialon-webservices-siscop_02              RUNNING
wialon-webservices-osinergmin:wialon-webservices-osinergmin_00      RUNNING
wialon-webservices-osinergmin:wialon-webservices-osinergmin_01      RUNNING
wialon-webservices-osinergmin:wialon-webservices-osinergmin_02      RUNNING
wialon-webservices-sutran:wialon-webservices-sutran_00              RUNNING
wialon-webservices-sutran:wialon-webservices-sutran_01              RUNNING
wialon-webservices-sutran:wialon-webservices-sutran_02              RUNNING
wialon-webservices-reenvio:wialon-webservices-reenvio_00            RUNNING
wialon-webservices-reenvio:wialon-webservices-reenvio_01            RUNNING
```

### Monitorear Logs

```bash
# Logs de Laravel
tail -f /var/www/wialon-webservices/storage/logs/laravel.log

# Logs de Workers
tail -f /var/www/wialon-webservices/storage/logs/worker-*.log

# Logs de Apache
tail -f /var/www/wialon-webservices/storage/logs/apache-*.log
```

---

## 🔧 Comandos Útiles

### Gestión de Supervisor

```bash
# Ver estado de todos los workers
sudo supervisorctl status

# Reiniciar todos los workers
sudo supervisorctl restart all

# Reiniciar worker específico
sudo supervisorctl restart wialon-webservices-siscop:*

# Detener todos los workers
sudo supervisorctl stop all

# Iniciar todos los workers
sudo supervisorctl start all

# Ver logs de un worker
sudo supervisorctl tail wialon-webservices-web-service
```

### Gestión de Colas Laravel

```bash
cd /var/www/wialon-webservices

# Ver trabajos en cola
php artisan queue:monitor redis:web-service,siscop-queue,osinergmin-queue,sutran-queue,reenviar-historial

# Limpiar trabajos fallidos
php artisan queue:flush

# Reintentar trabajos fallidos
php artisan queue:retry all

# Ver estadísticas de colas
php artisan queue:work --once --verbose
```

### Limpiar Cachés

```bash
cd /var/www/wialon-webservices

# Limpiar todos los cachés
php artisan optimize:clear

# O individualmente:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear
```

### Mantenimiento

```bash
# Modo mantenimiento ON
php artisan down --message="Mantenimiento programado" --retry=60

# Modo mantenimiento OFF
php artisan up
```

---

## 🔐 Seguridad Adicional (Recomendado)

### Configurar SSL con Let's Encrypt

```bash
# Instalar Certbot
sudo dnf install -y certbot python3-certbot-apache

# Obtener certificado SSL
sudo certbot --apache -d tu-dominio.com -d www.tu-dominio.com

# El certificado se renovará automáticamente
```

### Configurar Fail2Ban (Opcional)

```bash
# Instalar Fail2Ban
sudo dnf install -y fail2ban

# Crear configuración
sudo tee /etc/fail2ban/jail.d/apache.conf > /dev/null <<'EOF'
[apache-auth]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/www/wialon-webservices/storage/logs/apache-error.log
maxretry = 3
bantime = 3600
EOF

sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

---

## 🐛 Solución de Problemas

### Workers no inician

```bash
# Ver logs de Supervisor
sudo tail -f /var/log/supervisor/supervisord.log

# Verificar permisos
ls -la /var/www/wialon-webservices/storage

# Reiniciar Supervisor
sudo systemctl restart supervisord
```

### Apache no sirve la aplicación

```bash
# Verificar configuración de Apache
sudo apachectl configtest

# Ver logs de error
sudo tail -f /var/www/wialon-webservices/storage/logs/apache-error.log

# Verificar permisos de SELinux
sudo ausearch -m avc -ts recent
```

### Redis no conecta

```bash
# Verificar estado de Redis
sudo systemctl status redis

# Probar conexión
redis-cli ping
# Debe responder: PONG

# Ver logs de Redis
sudo tail -f /var/log/redis/redis.log
```

### Base de datos no conecta

```bash
# Verificar estado de MariaDB
sudo systemctl status mariadb

# Probar conexión
mysql -u wialon_user -p wialon_webservices

# Ver logs de MariaDB
sudo tail -f /var/log/mariadb/mariadb.log
```

---

## 📚 Recursos Adicionales

-   [Repositorio GitHub](https://github.com/Jhamnerx/wialon-webservices)
-   [Documentación de Laravel](https://laravel.com/docs/11.x)
-   [Documentación de Supervisor](http://supervisord.org/)
-   [Documentación de Redis](https://redis.io/documentation)
-   [Documentación de Apache](https://httpd.apache.org/docs/)
-   [AlmaLinux Documentation](https://wiki.almalinux.org/)

---

## ✅ Checklist Post-Instalación

-   [ ] PHP 8.3 instalado y verificado
-   [ ] MariaDB instalado y configurado
-   [ ] Redis instalado y funcionando
-   [ ] Git instalado
-   [ ] Repositorio clonado desde GitHub en `/var/www/wialon-webservices`
-   [ ] Dependencias de Composer y npm instaladas
-   [ ] Assets compilados con npm run build
-   [ ] Apache configurado con VirtualHost
-   [ ] PHP-FPM configurado con usuario root
-   [ ] Supervisor con 15 workers activos (user=root)
-   [ ] Cron configurado para scheduler y limpieza (en crontab de root)
-   [ ] SELinux deshabilitado
-   [ ] Archivo .env configurado con credenciales correctas (usuario root de MariaDB)
-   [ ] Clave de aplicación generada
-   [ ] Migraciones ejecutadas
-   [ ] Seeders ejecutados (si es instalación nueva)
-   [ ] Aplicación optimizada para producción
-   [ ] Permisos configurados (root:apache)
-   [ ] SSL configurado (opcional pero recomendado)
-   [ ] Backups programados (recomendado)

---

**Instalación completada exitosamente! 🎉**
