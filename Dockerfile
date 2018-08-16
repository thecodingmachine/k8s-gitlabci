FROM alpine:3.7

ENV CLOUD_SDK_VERSION 206.0.0

ENV PATH /google-cloud-sdk/bin:$PATH

RUN apk --no-cache add \
        curl \
        python \
        py-crcmod \
        py2-pip
        bash \
        libc6-compat \
        openssl \
        openssh-client \
        git \
        gettext \
        jq \
        ca-certificates

# install docker
COPY --from=docker:18 /usr/local/bin/docker* /usr/bin/

RUN pip install --upgrade pip \
    && pip install docker-compose

RUN curl -L https://raw.githubusercontent.com/kubernetes/helm/master/scripts/get | bash; \
    helm init --client-only

# Install kubectl
RUN curl -LO https://storage.googleapis.com/kubernetes-release/release/$(curl -s https://storage.googleapis.com/kubernetes-release/release/stable.txt)/bin/linux/amd64/kubectl

RUN curl -O https://dl.google.com/dl/cloudsdk/channels/rapid/downloads/google-cloud-sdk-${CLOUD_SDK_VERSION}-linux-x86_64.tar.gz && \
    tar xzf google-cloud-sdk-${CLOUD_SDK_VERSION}-linux-x86_64.tar.gz && \
    rm google-cloud-sdk-${CLOUD_SDK_VERSION}-linux-x86_64.tar.gz && \
    ln -s /lib /lib64 && \
    gcloud config set core/disable_usage_reporting true && \
    gcloud config set component_manager/disable_update_check true && \
    gcloud config set metrics/environment github_docker_image && \
    gcloud --version

VOLUME ["/root/.config"]

# configure gcloud git helper for CSR usage
RUN git config --global credential.helper gcloud.sh

ADD delete_image.sh /delete_image.sh

RUN helm version --client && gcloud version

