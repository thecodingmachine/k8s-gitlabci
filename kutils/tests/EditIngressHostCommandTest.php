<?php

namespace Kutils;

use PHPUnit\Framework\TestCase;

class EditIngressHostCommandTest extends TestCase
{

    public function testEditIngress()
    {
        $result = EditIngressHostCommand::editIngress(<<<YAML
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress1
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: foo.bar.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
YAML
            , 'baz.com', null, null
        );

        $this->assertSame(<<<YAML
---
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress1
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: baz.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
...

YAML
        , $result);
    }

    public function testEditSpecificIngress()
    {
        $result = EditIngressHostCommand::editIngress(<<<YAML
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress1
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: foo.bar.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
---
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress2
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: foo.bar.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
YAML
            , 'baz.com', 'ingress2', null
        );

        $this->assertSame(<<<YAML
---
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress1
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: foo.bar.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
...
---
apiVersion: networking.k8s.io/v1beta1
kind: Ingress
metadata:
  name: ingress2
  annotations:
    nginx.ingress.kubernetes.io/rewrite-target: /
spec:
  rules:
  - host: baz.com
    http:
      paths:
      - path: /foo
        backend:
          serviceName: service1
          servicePort: 4200
...

YAML
            , $result);
    }
}
