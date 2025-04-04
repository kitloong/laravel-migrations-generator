ARG PHP_BUILD_VERSION
ARG PHP_TEST_VERSION

# Composer on correct PHP version
FROM php:${PHP_BUILD_VERSION}-cli AS build

ARG BUILD_ROOT_PATH
ARG NODE_VERSION

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN apt-get update
RUN apt-get install -y zip unzip curl git
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Node
RUN apt install -y curl
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
ENV NVM_DIR=/root/.nvm
RUN . "$NVM_DIR/nvm.sh" && nvm install $NODE_VERSION
RUN . "$NVM_DIR/nvm.sh" && nvm use v${NODE_VERSION}
RUN . "$NVM_DIR/nvm.sh" && nvm alias default v${NODE_VERSION}
ENV PATH="/root/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"
RUN node --version
RUN npm --version

# DBML cli
RUN npm install -g @dbml/cli

WORKDIR ${BUILD_ROOT_PATH}
COPY . ./


FROM php:${PHP_TEST_VERSION}-cli AS test

ARG BUILD_ROOT_PATH
ARG XDEBUG_VERSION

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN pecl install xdebug-${XDEBUG_VERSION}
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install posix

WORKDIR ${BUILD_ROOT_PATH}
COPY --from=build ${BUILD_ROOT_PATH} ${BUILD_ROOT_PATH}


# Install PHP dev dependencies
FROM build AS vendor-dev

ARG BUILD_ROOT_PATH

WORKDIR ${BUILD_ROOT_PATH}
RUN COMPOSER_DISCARD_CHANGES=true composer install
