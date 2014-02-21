<?php

namespace Uecode\Bundle\ImageBundle\DependencyInjection;

use Uecode\Bundle\ImageBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class UecodeImageExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('aws.s3', $config['aws']['s3']);
        unset($config['aws']);
        $container->setParameter('uecode_image', $config);

        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.yml');
        $loader->load('routing.yml');

        $this->createAwsClient($container->getParameter('aws.s3'), $container);
    }

    private function createAwsClient($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('uecode_image.provider.aws')) {

            if (!class_exists('Aws\S3\S3Client')) {
                throw new \RuntimeException(
                    'You must require "aws/aws-sdk-php" to use the AWS provider.'
                );
            }

            // Validate the config
            if (empty($config['key']) || empty($config['secret'])) {
                throw new \InvalidArgumentException(
                    'The `key` and `secret` must be set in your configuration file to use the AWS Provider'
                );
            }

            $aws = new Definition('Aws\S3\S3Client');
            $aws->setFactoryClass('Aws\S3\S3Client');
            $aws->setFactoryMethod('factory');
            $aws->setArguments([
                [
                    'key'      => $config['key'],
                    'secret'   => $config['secret'],
                    'region'   => $config['region']
                ]
            ]);

            // Expose Service to container
            $container->setDefinition('uecode_image.provider.aws', $aws)
                ->setPublic(true);

        } else {
            $aws = $container->getDefinition('uecode_image.provider.aws');
        }

        return $aws;
    }
}
