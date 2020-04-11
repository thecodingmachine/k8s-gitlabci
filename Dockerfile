FROM thecodingmachine/php:7.3-v2-cli AS builder
COPY --chown=docker:docker kutils .

ENV PHP_INI_PHAR__READONLY=Off
ENV PHP_EXTENSION_YAML=1

RUN curl -LSs https://box-project.github.io/box2/installer.php | php
RUN composer install --no-dev
RUN mkdir build
RUN ./box.phar build

FROM ubuntu:bionic

# Install PHP
RUN apt-get update -y && apt-get install -y --no-install-recommends php-cli php-yaml curl gnupg2 ca-certificates lsb-release

# Install GCloud SDK
RUN export CLOUD_SDK_REPO="cloud-sdk-$(lsb_release -c -s)" && \
    echo "deb http://packages.cloud.google.com/apt $CLOUD_SDK_REPO main" | tee -a /etc/apt/sources.list.d/google-cloud-sdk.list && \
    curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | apt-key add - && \
    apt-get update -y && apt-get install google-cloud-sdk kubectl -y

# TODO: there is a bug in kubectl 1.18.0 so we force another version.
# TODO: remove this when kubectl comes in a newer version by default
RUN curl -LO https://storage.googleapis.com/kubernetes-release/release/v1.18.1/bin/linux/amd64/kubectl \
 && chmod +x ./kubectl \
 && mv ./kubectl /usr/local/bin/kubectl

ENV KUBECONFIG=/root/.kube/config
RUN mkdir -p /root/.kube

COPY --from=builder /usr/src/app/build/kutils.phar /usr/local/bin/kutils
COPY delete_image.sh /usr/local/bin/delete_image
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
COPY connect_gcloud.sh /usr/local/bin/connect_gcloud
COPY connect.sh /usr/local/bin/connect

#RUN mkdir kutils
#COPY --from=builder /usr/src/app/ /kutils
#RUN ln -s /kutils/kutils.php /usr/local/bin/kutils
#RUN cd app/ && php kutils.php

# test installation
RUN kutils | grep kutils

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
