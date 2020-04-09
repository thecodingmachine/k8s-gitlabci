#!/bin/bash

set -e

echoerr() {
  if [[ "$SILENT_WARNINGS" != "1" ]]; then
    echo "$@" 1>&2;
  fi;
}

echoerr "Performing automatic configuration for Kubernetes cluster"
echo "$KUBE_CONFIG_FILE" > /root/.kube/config
