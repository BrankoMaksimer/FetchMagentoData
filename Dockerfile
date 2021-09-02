FROM php:7.3-fpm

RUN apt-get update && apt-get install -y \
    wget \
    build-essential \
    libmp3lame-dev \
    libvorbis-dev \
    libtheora-dev \
    libspeex-dev \
    yasm \
    pkg-config \
    libx264-dev \
    libfreetype6 \
    libfreetype6-dev \
    libfribidi-dev \
    libfontconfig1-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    libxrender1 \
    libxext6 \
    pkg-config \
    software-properties-common

RUN apt-get update && apt-get install -y \
    && docker-php-ext-install gd \
    && docker-php-ext-install mbstring \
    && docker-php-ext-enable gd

RUN pecl install redis-3.1.4 \
    && docker-php-ext-enable redis
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli

RUN apt update && apt install curl && \
    curl -sS https://getcomposer.org/installer | php \
    && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN apt-get -y update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN apt-get update && apt-get install -y \
    libfreetype6-dev libjpeg62-turbo-dev \
    libgd-dev libpng-dev
RUN docker-php-ext-configure gd \
    --with-freetype-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install gd

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip


RUN apt-get update && apt-get install --yes --no-install-recommends \
    libssl-dev

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb