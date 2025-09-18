# Utiliser une image de base PHP avec Apache
FROM php:8.2-apache

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite

# Copier les fichiers de l'application dans le répertoire de l'hôte Apache
COPY . /var/www/html/

# Donner les permissions appropriées au répertoire et aux fichiers
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

# Exposer le port 80 pour le serveur web
EXPOSE 80

# Commande par défaut pour démarrer Apache
CMD ["apache2-foreground"]

