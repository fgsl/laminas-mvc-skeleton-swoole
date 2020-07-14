FROM php:7.4.2-cli

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install apt-utils -y && \
    apt-get install dialog -y && \
    apt-get install vim -y && \
    apt-get install apt-utils -y && \
    apt-get install openssl -y && \
    apt-get install libssl-dev -y && \
    apt-get install wget -y && \
    apt-get install git -y && \
    apt-get install procps -y && \
    apt-get install htop -y && \
    apt-get install libicu-dev -y

RUN cd /tmp && git clone https://github.com/swoole/swoole-src.git && \
    cd swoole-src && \
    git checkout v4.5.2 && \
    phpize  && \
    ./configure  --enable-openssl && \
    make && make install

RUN touch /usr/local/etc/php/conf.d/swoole.ini && \
    echo 'extension=swoole.so' > /usr/local/etc/php/conf.d/swoole.ini

RUN wget -O /usr/local/bin/dumb-init https://github.com/Yelp/dumb-init/releases/download/v1.2.2/dumb-init_1.2.2_amd64
RUN chmod +x /usr/local/bin/dumb-init

RUN docker-php-ext-install intl && \
    docker-php-ext-enable intl

RUN apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

ENTRYPOINT ["/usr/local/bin/dumb-init", "--", "php"]
