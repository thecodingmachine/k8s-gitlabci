<?php


namespace Kutils;


use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function count;
use function strpos;
use function yaml_emit;
use function yaml_parse;

class AddSecretCommand extends Command
{
    use FileUtils;

    protected static $defaultName = 'secret:add';

    protected function configure()
    {
        $this
            ->setDescription('Add a secret in a YAML secret file')
            ->setHelp(<<<HELP
This command allows you to add a secret in a secrets.yaml file.

Usage:

    kutils secret:add secret.yaml --secret-name=DB_PASSWORD --secret-value=foobar [--name=my-secrets]
    kutils secret:add secret.yaml --secret-name=DB_PASSWORD --secret-value-from-env=MYSQL_PASSWORD
HELP
            )
            ->addArgument('yamlFile', InputArgument::REQUIRED, 'Path to the YAML secrets file')
            ->addOption('secret-name', null, InputOption::VALUE_REQUIRED, 'The name of the credential to store', null)
            ->addOption('secret-value', null, InputOption::VALUE_OPTIONAL, 'The value of the credential to store', null)
            ->addOption('secret-value-from-env', null, InputOption::VALUE_OPTIONAL, 'Takes the value to store from an environment variable', null)
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'The name of the Secret resource (used if there are multiple documents in the YAML file)', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = file_get_contents($input->getArgument('yamlFile'));

        $value = null;

        $envVarName = $input->getOption('secret-value-from-env');
        if ($envVarName !== null && isset($_SERVER[$envVarName])) {
            $value = $_SERVER[$envVarName];
        } else {
            $value = $input->getOption('secret-value');
        }

        if ($value === null) {
            throw new \RuntimeException('You need to pass at least option between --secret-value and --secret-value-from-env. If you use --secret-value-from-env, the environment variable must exist (it can be empty).');
        }

        $content = self::addSecret($content, $input->getOption('secret-name'), $value, $input->getOption('name'));

        file_put_contents($input->getArgument('yamlFile'), $content);
    }

    public static function addSecret(string $yaml, string $credentialName, string $credentialValue, ?string $secretName): string
    {
        $resources = yaml_parse($yaml, -1, $nbDocs);

        /*$count = 0;

        foreach ($resources as $resource) {
            if (isset($resource['kind']) && $resource['kind'] === 'Ingress') {
                $count++;
            }
        }

        if ($ingressName === null && $count > 1) {
            throw new \RuntimeException('The YAML file contains more than one ingress. You must use the --ingress-name option to specify the ingress to use.');
        }*/

        if ($secretName !== null) {
            $index = self::findResourceByName($resources, $secretName, 'Secret');
        } else {
            $index = self::findResourceByType($resources, 'Secret');
        }

        unset($resources[$index]['data'][$credentialName]);

        $resources[$index]['stringData'][$credentialName] = $credentialValue;

        $yaml = '';
        foreach ($resources as $resource) {
            $yaml .= yaml_emit($resource);
        }
        return $yaml;
    }
}
