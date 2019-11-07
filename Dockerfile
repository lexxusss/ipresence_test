FROM php:7-alpine
ENV COMPOSER_PATH=/usr/local/bin

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=$COMPOSER_PATH \
    && php -r "unlink('composer-setup.php');"
