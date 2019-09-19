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

class EditResources extends Command
{
    use FileUtils;

    protected static $defaultName = 'common:edit-limits';

    protected function configure()
    {
        $this
            ->setDescription('Edits the requests and limits of a pod / deployment / ...')
            ->setHelp(<<<HELP
This command allows you to set the resources requests and limits 

Usage:

    kutils ingress:edit-host [yaml file] [new host] [--ingress-name=my-ingress] [--host-position=0]
HELP
            )
            ->addArgument('yamlFile', InputArgument::REQUIRED, 'The YAML file to edit')
            ->addOption('resource-name', null, InputOption::VALUE_OPTIONAL, 'The name of the resource to edit. If not specified and there is only one resource, this resource will be selected.', null)
            ->addOption('container-name', null, InputOption::VALUE_OPTIONAL, 'The name of the container to edit. If the pod contains only one container, this container will be selected.', null)
            ->addOption('cpu-limit', null, InputOption::VALUE_OPTIONAL, 'The CPU limit', null)
            ->addOption('memory-limit', null, InputOption::VALUE_OPTIONAL, 'The memory limit', null)
            ->addOption('cpu-request', null, InputOption::VALUE_OPTIONAL, 'The CPU request', null)
            ->addOption('memory-request', null, InputOption::VALUE_OPTIONAL, 'The memory request', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = file_get_contents($input->getArgument('yamlFile'));

        $content = self::editRequestsLimits($content,
            $input->getOption('resource-name'),
            $input->getOption('container-name'),
            $input->getOption('cpu-limit'),
            $input->getOption('memory-limit'),
            $input->getOption('cpu-request'),
            $input->getOption('memory-request'));
        file_put_contents($input->getArgument('yamlFile'), $content);
    }

    public static function editRequestsLimits(string $yaml, ?string $resourceName, ?string $containerName, ?string $cpuLimit, ?string $memoryLimit, ?string $cpuRequest, ?string $memoryRequest): string
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
}
