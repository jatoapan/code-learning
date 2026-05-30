FROM php:8.3-apache

# Evitar prompts interactivos durante la instalación de paquetes
ENV DEBIAN_FRONTEND=noninteractive

# Instalar dependencias del sistema requeridas para Moodle y sus extensiones de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libsodium-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar las extensiones de PHP necesarias para Moodle
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    mysqli \
    zip \
    intl \
    soap \
    opcache \
    sodium \
    exif

# Crear el directorio de datos de Moodle
RUN mkdir -p /var/moodledata && \
    chown -R www-data:www-data /var/moodledata && \
    chmod -R 777 /var/moodledata

# Configurar Apache para que use /var/www/html/public como su DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Habilitar rewrite (MPM config moved to entrypoint)
RUN a2enmod rewrite

# Copiar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar el código del proyecto al contenedor
WORKDIR /var/www/html
COPY . /var/www/html

# Copiar plugins personalizados si existen
RUN if [ -d "plugins" ] && [ "$(ls -A plugins | grep -v 'README.md')" ]; then \
        cp -r plugins/* public/; \
    fi

# Instalar dependencias de Composer
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

# Create entrypoint script to fix MPM and volume permissions at runtime
RUN echo '#!/bin/bash\n\
chown -R www-data:www-data /var/moodledata && chmod -R 777 /var/moodledata\n\
a2dismod mpm_event mpm_worker 2>/dev/null || true\n\
a2enmod mpm_prefork\n\
exec apache2-foreground' > /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer el puerto 80
EXPOSE 80

# Definir el entrypoint de inicio
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
