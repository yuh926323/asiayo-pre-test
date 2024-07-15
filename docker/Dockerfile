FROM php:8-fpm

WORKDIR /works/asiayo-pre-test
COPY . /works/asiayo-pre-test
COPY .env.example /works/asiayo-pre-test/.env

RUN apt-get update && apt-get install -y \
        # For php zip ext
        zlib1g-dev \
        libzip-dev \
        unzip \
        && docker-php-ext-configure zip \
        && docker-php-ext-install -j$(nproc) zip \
        && pecl install xdebug-3.3.0 \
        && docker-php-ext-enable xdebug

# For composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
# For timezone
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

# composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-plugins --no-scripts

# laravel key
RUN php artisan key:generate

# For unit test coverage report
RUN echo "xdebug.mode=coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN chmod +x ./docker/docker-entrypoint.sh

ENTRYPOINT ["/works/asiayo-pre-test/docker/docker-entrypoint.sh"]