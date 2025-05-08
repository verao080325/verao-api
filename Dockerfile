FROM php:8.1-apache

# Copia todos os arquivos para o container
COPY . /var/www/html/

# Ativa o módulo de reescrita do Apache (caso precise futuramente)
RUN a2enmod rewrite

# Define o DocumentRoot como a pasta Public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/Public|' /etc/apache2/sites-available/000-default.conf

# Ajusta as permissões
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
