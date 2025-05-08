FROM php:8.2-apache

# Instalar extensão openssl
RUN docker-php-ext-install openssl

# Copiar arquivos públicos para o Apache root
COPY Public/ /var/www/html/

# Copiar lógica e chaves (não acessível pela web)
COPY App/ /var/www/app/
COPY Keys/ /var/www/keys/
COPY Logs/ /var/www/logs/

# Permissões e segurança
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www
