#!/bin/bash

set -e

>&2 echo "Performing automatic configuration for Kubernetes cluster"
echo "$KUBE_CONFIG_FILE" > /root/.kube/config
