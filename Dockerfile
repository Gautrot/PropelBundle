FROM ubuntu:22.04

ENV SYMFONY_REQUIRE=6.4
ENV PHP=8.2
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /app

RUN ln -snf /usr/share/zoneinfo/$CONTAINER_TIMEZONE /etc/localtime \
    && echo $CONTAINER_TIMEZONE > /etc/timezone
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt update
RUN apt install -y software-properties-common
RUN add-apt-repository -y ppa:ondrej/php
RUN apt update && apt upgrade -y
RUN apt install -y git mysql-common nano postgresql sqlite sudo zip php$PHP \
    php$PHP-cli \
    php$PHP-ctype \
    php$PHP-curl \
    php$PHP-dom \
    php$PHP-iconv \
    php$PHP-intl \
    php$PHP-pdo \
    php$PHP-mbstring \
    php$PHP-mysql \
    php$PHP-pgsql \
    php$PHP-simplexml \
    php$PHP-sqlite3 \
    php$PHP-tokenizer \
    php$PHP-xml \
    php$PHP-xmlwriter

RUN apt autoremove -y && apt autoclean -y
RUN update-alternatives --set php /usr/bin/php$PHP

COPY . /app
RUN composer global config --no-plugins allow-plugins.symfony/flex true
RUN composer global require --no-progress --no-scripts --no-plugins symfony/flex
RUN SYMFONY_REQUIRE=$SYMFONY_REQUIRE composer u --prefer-dist -n

CMD ["sleep", "infinity"]
