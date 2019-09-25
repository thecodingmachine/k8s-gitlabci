<?php

namespace Kutils;

use PHPUnit\Framework\TestCase;

class AddSecretCommandTest extends TestCase
{

    public function testAddSecret()
    {
        $result = AddSecretCommand::addSecret(<<<YAML
apiVersion: v1
kind: Secret
metadata:
  name: mysecret
type: Opaque
data:
  username: YWRtaW4=
YAML
            , 'username', 'administrator', null
        );

        $this->assertSame(<<<YAML
---
apiVersion: v1
kind: Secret
metadata:
  name: mysecret
type: Opaque
data: []
stringData:
  username: administrator
...

YAML
        , $result);
    }

    public function testEditSpecificSecret()
    {
        $result = AddSecretCommand::addSecret(<<<YAML
apiVersion: v1
kind: Secret
metadata:
  name: mysecret
type: Opaque
data:
  username: YWRtaW4=
---
apiVersion: v1
kind: Secret
metadata:
  name: mysecret2
type: Opaque
data:
  username2: YWRtaW4=
YAML
            , 'username2', 'administrator', 'mysecret2'
        );

        $this->assertSame(<<<YAML
---
apiVersion: v1
kind: Secret
metadata:
  name: mysecret
type: Opaque
data:
  username: YWRtaW4=
...
---
apiVersion: v1
kind: Secret
metadata:
  name: mysecret2
type: Opaque
data: []
stringData:
  username2: administrator
...

YAML
            , $result);
    }
}
