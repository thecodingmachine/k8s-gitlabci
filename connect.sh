#!/bin/bash

set -e

echo "Configuring automatic configuration to Kubernetes cluster"
echo "$KUBE_CONFIG_FILE" > /root/.kube/config
