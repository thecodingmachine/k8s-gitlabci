#!/bin/bash

set -e

echo "Configuring automatic configuration to Kubernetes cluster"
mkdir -p /home/docker/.kube
echo "$KUBE_CONFIG_FILE" > /home/docker/.kube/config
