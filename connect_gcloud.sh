#!/bin/bash

set -e

echo "Configuring automatic configuration to Google Cloud Kubernetes cluster"
echo "$GCLOUD_SERVICE_KEY" > key.json
gcloud auth activate-service-account --key-file key.json
gcloud config set project $GCLOUD_PROJECT
gcloud container clusters get-credentials $GKE_CLUSTER --zone $GCLOUD_ZONE --project $GCLOUD_PROJECT
