#!/bin/bash

set -e

echoerr() {
  if [[ "$SILENT_WARNINGS" != "1" ]]; then
    echo "$@" 1>&2;
  fi;
}


if [ "$AUTOCONNECT" = "1" ] && [ -n "$GCLOUD_SERVICE_KEY" ] ; then
  connect_gcloud
elif [ "$AUTOCONNECT" = "1" ] && [ -n "$KUBE_CONFIG_FILE" ] ; then
  connect
else
  echoerr "No connection to a Kubernetes cluster was configured on container startup"
  echoerr "   To automatically create a connection, set environment variable AUTOCONNECT=1"
  echoerr "   Then, use the KUBE_CONFIG_FILE environment variable to provide your Kubernetes configuration file"
  echoerr "   Or use the GCLOUD_SERVICE_KEY environment variable to connect to Gcloud"
  echoerr "   More information: https://github.com/thecodingmachine/k8s-gitlabci"
fi

exec "$@"
