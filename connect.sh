#!/bin/bash

set -e

echoerr() {
  if [[ "$SILENT_WARNINGS" != "1" ]]; then
    echo "$@" 1>&2;
  fi;
}

if [ -n "$GCLOUD_SERVICE_KEY" ] ; then
  connect_gcloud
elif [ -n "$KUBE_CONFIG_FILE" ] ; then
  connect_standard
else
  echoerr "No connection to a Kubernetes cluster was configured"
  echoerr "   Use the KUBE_CONFIG_FILE environment variable to provide your Kubernetes configuration file"
  echoerr "   Or use the GCLOUD_SERVICE_KEY environment variable to connect to Gcloud"
  echoerr "   More information: https://github.com/thecodingmachine/k8s-gitlabci"
fi
