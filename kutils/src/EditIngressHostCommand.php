<?php


namespace Kutils;


use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function count;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use function Safe\yaml_parse_file;
use function yaml_emit;

class EditIngressHostCommand extends Command
{
    protected static $defaultName = 'ingress:edit-host';

    protected function configure()
    {
        $this
            ->setDescription('Changes the host in an Ingress file')
            ->setHelp(<<<HELP
This command allows you to edit any yaml file containing an ingress to change the host.

Usage:

    kutils ingress:edit-host [yaml file] [new host] [--ingress-name=my-ingress] [--host-position=0]
HELP
            )
            ->addArgument('yamlFile', InputArgument::REQUIRED, 'The YAML file to edit')
            ->addArgument('host', InputArgument::REQUIRED, 'The new value for the edited host')
            ->addOption('ingress-name', null, InputOption::VALUE_OPTIONAL, 'The name of the ingress resource. If not specified and there is only one ingress this ingress will be selected.', null)
            ->addOption('host-position', null, InputOption::VALUE_OPTIONAL, 'If there are many hosts in the ingress, you must pass the host position in the list', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = file_get_contents($input->getArgument('yamlFile'));
        $content = self::editIngress($content, $input->getArgument('host'), $input->getOption('ingress-name'), $input->getOption('host-position'));
        file_put_contents($input->getArgument('yamlFile'), $content);
    }

    public static function editIngress(string $yaml, string $host, ?string $ingressName, ?int $hostPosition): string
    {
        $resources = yaml_parse($yaml, -1, $nbDocs);

        $count = 0;

        foreach ($resources as $resource) {
            if (isset($resource['kind']) && $resource['kind'] === 'Ingress') {
                $count++;
            }
        }

        if ($ingressName === null && $count > 1) {
            throw new \RuntimeException('The YAML file contains more than one ingress. You must use the --ingress-name option to specify the ingress to use.');
        }

        if ($ingressName !== null) {
            $index = self::findResourceByName($resources, $ingressName, 'Ingress');
        } else {
            $index = self::findResourceByType($resources, 'Ingress');
        }

        $rules = $resources[$index]['spec']['rules'];

        if (count($rules) > 1 && $hostPosition === null) {
            throw new RuntimeException('There are more than one rule in the Ingress. Please specify the --host-position option');
        }
        if ($hostPosition === null) {
            $hostPosition = 0;
        }

        $resources[$index]['spec']['rules'][$hostPosition]['host'] = $host;

        $yaml = '';
        foreach ($resources as $resource) {
            $yaml .= yaml_emit($resource);
        }
        return $yaml;
    }

    /**
     * @param array $resources
     * @return int Index of the resource, by name
     */
    public static function findResourceByName(array $resources, string $name, string $type): int
    {
        foreach ($resources as $i => $resource) {
            if (isset($resource['kind']) && $resource['kind'] === $type && isset($resource['metadata']['name']) && $resource['metadata']['name'] === $name) {
                return $i;
            }
        }
        throw new RuntimeException('Could not find "'.$type.'" with name "'.$name.'"');
    }

    /**
     * @param array $resources
     * @return int Index of the resource, by name
     */
    public static function findResourceByType(array $resources, string $type): int
    {
        $foundResources = [];
        foreach ($resources as $i => $resource) {
            if (isset($resource['kind']) && $resource['kind'] === $type) {
                $foundResources[$i] = $resource;
            }
        }
        if (count($foundResources) === 0) {
            throw new RuntimeException('Could not find resource with type "'.$type.'"');
        }
        if (count($foundResources) > 1) {
            throw new RuntimeException('There are many resources with type "'.$type.'" in the YAML document');
        }
        foreach ($resources as $i => $resource) {
            return $i;
        }
    }
}
