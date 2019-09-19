#!/bin/bash

set -e

if [ "$AUTOCONNECT" = "1" ] && [ -n "$GCLOUD_SERVICE_KEY" ] ; then
  connect_gcloud
elif [ "$AUTOCONNECT" = "1" ] && [ -n "$KUBE_CONFIG_FILE" ] ; then
  connect
fi

exec "$@"
