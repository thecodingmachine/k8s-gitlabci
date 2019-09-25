# K8S Gitlab-CI

A Kubectl image bundled with the Google Cloud console.

Image on Docker hub: [thecodingmachine/k8s-gitlabci](https://hub.docker.com/r/thecodingmachine/k8s-gitlabci)

It also feature useful scripts to easily create secrets and a script to delete images in Gitlab Registry.

## Connecting to a GCloud environment

You can automate connection to a GKE cluster by setting these environment variables:

- GCLOUD_SERVICE_KEY
- GCLOUD_PROJECT
- GCLOUD_ZONE
- GKE_CLUSTER

Then, simply call:
 
```bash
# connect_gcloud
```

Or if you want to connect automatically on container startup, set the environment variable:

- AUTOCONNECT=1

## Connecting to a "standard" environment

If a "kubeconfig" file is enough to connect to your environement, you can automate connection to your cluster
by setting the `KUBE_CONFIG_FILE` environment variable.

- KUBE_CONFIG_FILE should contain the content of the *kubeconfig* file.

Then, simply call:
 
```bash
# connect
```

Or if you want to connect automatically on container startup, set the environment variable:

- AUTOCONNECT=1

## Creating secrets from CI environment variables

Any environment variable starting with "K8S_SECRET_" in Gilab can be ported to a Kubernetes secret.

Usage:

```bash
$ kutils secret:create --name=my_secrets_name > secret.yaml
```

If you want to change the prefix (for instance to get all environment variables starting with "K8S_DB_SECRET_"), use:

```bash
$ kutils secret:create --name=my_secrets_name --prefix=K8S_DB_SECRET_ > secret.yaml
```

## Adding a secret a a secrets YAML file

If you already have a secrets YAML file and you want to edit it, you can use the `secret:add` command.


Usage:

```bash
$ kutils secret:add secret.yaml --secret-name=DB_PASSWORD --secret-value=foobar [--name=my-secrets]
```

The "--name" is optional and can be used to specify the name of the secret resource to edit (in case your YAML file contains multiple documents with multiple secrets).

You can also ask `secret:add` to populate the secret from the content of an environment variable:

```bash
$ kutils secret:add secret.yaml --secret-name=DB_PASSWORD --secret-value-from-env=MYSQL_PASSWORD
```

In the example above, the `$MYSQL_PASSWORD` environment variable will be turned in a secret whose name is "DB_PASSWORD".

## Editing the host in an Ingress file

You can change the "host" of an Ingress file with a single command:

```bash
$ kutils ingress:edit-host ingress.yaml https://example.com
```

If your file contains many Ingresses, or if your Ingress contains many rules with many hosts, use:

```bash
$ kutils ingress:edit-host ingress.yaml  https://example.com --ingress-name=my-ingress --host-position=0
```

## Deleting images

Out of the box, there is no easy way to delete a special tag of a given image in the Gitlab registry (as of version 10.8).

This image provides a simple script that enables you to delete images easily.
 
## Why?

If you want to do continuous deployment, it is not uncommon to build one image per pipeline in Gitlab. You will typically
tag all your images using the commit SHA or the branch name. You will soon end up having a lot of images in your Gitlab 
registry. Docker images are big, and disk-space is finite so at some point, you will need to have a mechanism to 
automatically delete an image when it is no more needed.

As it turns out, deleting an image is surprisingly difficult, due to a number of outstanding issues:

 - [#20176 - Provide a programmatic method to delete images/tags from the registry](https://gitlab.com/gitlab-org/gitlab-ce/issues/20176)
 - [#21608 - Container Registry API](https://gitlab.com/gitlab-org/gitlab-ce/issues/21608)
 - [#25322 - Create a mechanism to clean up old container image revisions](https://gitlab.com/gitlab-org/gitlab-ce/issues/25322)
 - [#28970 - Delete from registry images for merged branches](https://gitlab.com/gitlab-org/gitlab-ce/issues/28970)
 - [#39490 - Allow to bulk delete docker images](https://gitlab.com/gitlab-org/gitlab-ce/issues/39490)
 - [#40096 - pipeline user $CI_REGISTRY_USER lacks permission to delete its own images](https://gitlab.com/gitlab-org/gitlab-ce/issues/40096)

This image is here to help.

## Usage

You will typically use this image in your `.gitlab-ci.yml` file.

**.gitlab-ci.yml**
```yml
delete_image:
  stage: cleanup
  image: thecodingmachine/k8s-gitlabci:latest
  script:
    - /delete_image.sh registry.gitlab.mycompany.com/path/to/image:$CI_COMMIT_REF_NAME
  when: manual
  environment:
    name: review/$CI_COMMIT_REF_NAME
    action: stop
  only:
  - branches
  except:
  - master
```

The `/delete_image.sh` script takes one single argument: the full path to the image to be deleted (including the tag).

**Important**: for the script to work, you must add a "Secret variable" in Gitlab CI named `CI_ACCOUNT`.
This variable must be in the form `[user]:[password]` where [user] is a Gitlab user that has access to the registry
and [password] is the Gitlab password of the user. This can be regarded obviously as a security issue if you don't trust
all developers who have access to the CI environment (as they will be able to "echo" this secret variable).

This is needed because the default Gitlab registry token available to the CI does not have the rights to delete
an image by default. An issue is opened in Gitlab to fix this issue: [#39490 - Allow to bulk delete docker images](https://gitlab.com/gitlab-org/gitlab-ce/issues/39490)

## Special thanks

All the hard work has been done by [Alessandro Lai](https://engineering.facile.it/blog/eng/continuous-deployment-from-gitlab-ci-to-k8s-using-docker-in-docker/#the-scary-part-deleting-docker-images)
and [Vincent Composieux](https://gitlab.com/gitlab-org/gitlab-ce/issues/21608#note_53674456).

I've only put your ideas in a Docker image.

## Miscellaneous

This image also contains `kubectl` (the command line tool for Kubernetes) that can be useful to perform cleanup actions
in a Kubernetes cluster.
