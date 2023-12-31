# Dockerイメージの指定
FROM php:8.1-fpm
EXPOSE 5173
COPY php.ini /usr/local/etc/php/

# パッケージとライブラリのインストール
RUN apt-get update \
    && apt-get install -y zlib1g-dev mariadb-client vim libzip-dev \
    && docker-php-ext-install zip pdo_mysql

# Composerのインストール
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN docker-php-ext-install pdo_mysql mysqli exif
RUN cd /usr/bin && curl -s http://getcomposer.org/installer | php && ln -s /usr/bin/composer.phar /usr/bin/composer

# Node.jsとNPMのインストール
ENV NODE_VERSION=18.17.0
RUN groupadd -g 1000 node \
    && useradd -u 1000 -g node -m -s /bin/bash node \
    && mkdir /home/node/.nvm
RUN chown -R node:node /home/node/.nvm
USER node
RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.38.0/install.sh | bash \
    && export NVM_DIR="/home/node/.nvm" \
    && [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" \
    && nvm install $NODE_VERSION \
    && nvm alias default $NODE_VERSION \
    && nvm use default
ENV PATH="/home/node/.nvm/versions/node/v${NODE_VERSION}/bin/:${PATH}"

# WorkDirの設定
WORKDIR /var/www/

# Laravelパッケージのインストールとエントリーポイント
RUN composer global require "laravel/installer"
ENTRYPOINT [ "bash", "-c", "composer install; npm install; exec php-fpm" ]
