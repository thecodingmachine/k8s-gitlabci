FROM thecodingmachine/php:7.3-v2-cli AS builder
COPY --chown=docker:docker kutils .

ENV PHP_INI_PHAR__READONLY=Off
ENV PHP_EXTENSION_YAML=1

#RUN curl -LSs https://box-project.github.io/box2/installer.php | php
RUN composer install --no-dev
#RUN ./box.phar build

FROM claranet/gcloud-kubectl-docker

RUN apk add --update ca-certificates \
 && apk add --update curl \
 && apk add --update gettext \
 && apk add --update bash \
 && rm /var/cache/apk/*

#ADD https://dl.bintray.com/php-alpine/key/php-alpine.rsa.pub /etc/apk/keys/php-alpine.rsa.pub
#RUN apk --update add ca-certificates
#RUN echo "https://dl.bintray.com/php-alpine/v3.9/php-7.3" >> /etc/apk/repositories
RUN apk add --update php7 \
 && apk add --update php7-pecl-yaml \
 && apk add --update php7-iconv

COPY delete_image.sh /delete_image.sh
COPY create_secret.sh /usr/local/bin/create_secret
#COPY --from=builder /usr/src/app/build/kutils.phar /usr/local/bin/kutils

# fix work iconv library with alphine
RUN apk add --no-cache --repository http://dl-cdn.alpinelinux.org/alpine/edge/community/ --allow-untrusted gnu-libiconv
ENV LD_PRELOAD /usr/lib/preloadable_libiconv.so php

RUN mkdir kutils
COPY --from=builder /usr/src/app/ /kutils
RUN ln -s /kutils/kutils.php /usr/local/bin/kutils
#RUN cd app/ && php kutils.php

# test installation
RUN kutils | grep kutils
