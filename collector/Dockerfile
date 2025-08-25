FROM php:8.2-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    sysstat \
    procps \
    net-tools \
    iputils-ping \
    curl \
    unzip \
    netcat-openbsd \
    gawk \
    sed \
    grep \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensões PHP necessárias
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar arquivos do projeto
COPY . /app
WORKDIR /app

# Instalar dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Criar script de inicialização
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
