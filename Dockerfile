FROM php:8.4-cli

ARG COOLIFY_FQDN
ARG APP_NAME
ARG APP_ENV
ARG APP_KEY
ARG APP_DEBUG
ARG APP_URL
ARG APP_LOCALE
ARG APP_FALLBACK_LOCALE
ARG APP_FAKER_LOCALE
ARG APP_MAINTENANCE_DRIVER
ARG BCRYPT_ROUNDS
ARG LOG_CHANNEL
ARG LOG_STACK
ARG LOG_DEPRECATIONS_CHANNEL
ARG LOG_LEVEL
ARG DB_CONNECTION
ARG DB_HOST
ARG DB_PORT
ARG DB_DATABASE
ARG DB_USERNAME
ARG DB_PASSWORD
ARG DB_PROTHEUS_HOST
ARG DB_PROTHEUS_PORT
ARG DB_PROTHEUS_DATABASE
ARG DB_PROTHEUS_USERNAME
ARG DB_PROTHEUS_PASSWORD
ARG SESSION_DRIVER
ARG SESSION_LIFETIME
ARG SESSION_ENCRYPT
ARG SESSION_PATH
ARG SESSION_DOMAIN
ARG BROADCAST_CONNECTION
ARG FILESYSTEM_DISK
ARG QUEUE_CONNECTION
ARG CACHE_STORE
ARG MEMCACHED_HOST
ARG REDIS_CLIENT
ARG REDIS_HOST
ARG REDIS_PASSWORD
ARG REDIS_PORT
ARG MAIL_MAILER
ARG MAIL_SCHEME
ARG MAIL_HOST
ARG MAIL_PORT
ARG MAIL_USERNAME
ARG MAIL_PASSWORD
ARG MAIL_FROM_ADDRESS
ARG MAIL_FROM_NAME
ARG AWS_ACCESS_KEY_ID
ARG AWS_SECRET_ACCESS_KEY
ARG AWS_DEFAULT_REGION
ARG AWS_BUCKET
ARG AWS_USE_PATH_STYLE_ENDPOINT
ARG VITE_APP_NAME

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    autoconf \
    g++ \
    make \
    gnupg2 \
    curl \
    apt-transport-https \
    unixodbc \
    unixodbc-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install Microsoft ODBC Driver for SQL Server (msodbcsql18)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y msodbcsql18 mssql-tools18 \
    && echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions: sqlsrv, pdo_sqlsrv, pdo_mysql, mbstring, exif, pcntl, bcmath, gd, zip
RUN pecl channel-update pecl.php.net \
    && pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction || true

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
