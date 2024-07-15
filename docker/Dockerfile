FROM php:8-fpm

WORKDIR /works/asiayo-pre-test
COPY . /works/asiayo-pre-test

# For composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer
# For timezone
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime && echo ${TZ} > /etc/timezone

# composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-plugins --no-scripts

RUN chmod +x ./docker/docker-entrypoint.sh

ENTRYPOINT ["/works/asiayo-pre-test/docker/docker-entrypoint.sh"]