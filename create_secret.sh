#!/bin/bash

set -e

declare -a PARAMS_ARRAY

for ENVVAR in $(compgen -A variable | grep K8S_SECRET_)
do
  NEWENVVAR=$(echo $ENVVAR | cut -c12-)

  PARAMS_ARRAY+=(--from-literal=$NEWENVVAR=${!ENVVAR@Q})
done

echo "Deleting secret"
kubectl delete secret "$@" || true

echo "Creating secret"
echo kubectl create secret generic "$@" "${PARAMS_ARRAY[@]/#/}" | bash
