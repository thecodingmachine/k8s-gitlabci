<?php


namespace Kutils;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function strpos;

class CreateSecretsCommand extends Command
{
    protected static $defaultName = 'secret:create';

    protected function configure()
    {
        $this
            ->setDescription('Outputs a YAML file containing secrets generated from the environment variables')
            ->setHelp(<<<HELP
This command allows you to create a secrets.yaml file from all environment variables starting with a given prefix.

Usage:

    kutils secret:create --prefix=K8S_SECRET_ --name=my_secrets > secret.yaml
HELP
            )
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The name of the secrets resource to be created', 'secrets')
            ->addOption('prefix', null, InputOption::VALUE_OPTIONAL, 'Only consider environment variables with given prefix', 'K8S_SECRET_');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resource = [
            'apiVersion' => 'v1',
            'kind' => 'Secret',
            'metadata' => [
                'name' => $input->getOption('name')
            ],
            'type' => 'Opaque',
            'stringData' => []
        ];

        $prefix = $input->getOption('prefix');

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $variableName = substr($key, strlen($prefix));
                $resource['stringData'][$variableName] = $value;
            }
        }

        echo yaml_emit($resource);
    }
}
