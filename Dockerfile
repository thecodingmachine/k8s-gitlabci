FROM claranet/gcloud-kubectl-docker

RUN apk add --update ca-certificates \
 && apk add --update curl \
 && apk add --update gettext \
 && apk add --update bash \
 && rm /var/cache/apk/*

COPY delete_image.sh /delete_image.sh

