#!/bin/bash

set -e

if [ "$AUTOCONNECT" = "1" ] && [ -n "$GCLOUD_SERVICE_KEY" ] ; then
  connect_gcloud
elif [ "$AUTOCONNECT" = "1" ] && [ -n "$KUBE_CONFIG_FILE" ] ; then
  connect
else
  >&2 echo "No connection to a Kubernetes cluster was configured on container startup"
  >&2 echo "   To automatically create a connection, set environment variable AUTOCONNECT=1"
  >&2 echo "   Then, use the KUBE_CONFIG_FILE environment variable to provide your Kubernetes configuration file"
  >&2 echo "   Or use the GCLOUD_SERVICE_KEY environment variable to connect to Gcloud"
  >&2 echo "   More information: https://github.com/thecodingmachine/k8s-gitlabci"
fi

exec "$@"
