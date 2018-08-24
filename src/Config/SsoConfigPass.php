<?php

namespace App\Config;

use App\Model\ConfigModel;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class SsoConfigPass implements CompilerPassInterface
{
    /**
     * The config file name.
     */
    private const FILE_NAME = 'config.yaml';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->injectConfigIntoConfigModel($container);
    }

    /**
     * Load the 'config.yaml' file and inject its content into the ConfigModel service.
     *
     * @param ContainerBuilder $container
     */
    private function injectConfigIntoConfigModel(ContainerBuilder $container): void
    {
        $filePath = $container->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . self::FILE_NAME;
        $configInput = Yaml::parseFile($filePath);
        $configProcessor = new Processor();
        $config = $configProcessor->processConfiguration(new SsoConfigDefinition(), [ $configInput['sso'] ]);

        $configModelDefinition = $container->getDefinition(ConfigModel::class);
        $configModelDefinition->setArgument(0, $config);
    }
}
