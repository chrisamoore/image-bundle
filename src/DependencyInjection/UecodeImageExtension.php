<?php

/**
 * Copyright 2014 Underground Elephant
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     image-bundle
 * @copyright   Underground Elephant 2014
 * @license     Apache License, Version 2.0
 */

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
 * Class UecodeImageExtension
 *
 * @author Chris Moore <chrisamoore@gmail.com>
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class UecodeImageExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $container->setParameter('aws.s3', $config['aws']['s3']);
        $container->setParameter('aws.s3.bucket', $config['aws']['s3']['bucket']);
        $container->setParameter('aws.s3.directory', $config['aws']['s3']['directory']);

        $container->setParameter('uecode_image.route', $config['route']);
        $container->setParameter('uecode_image.upload_dir', $config['upload_dir']);
        $container->setParameter('uecode_image.tmp_dir', $config['tmp_dir']);
        $container->setParameter('uecode_image.use_queue', $config['use_queue']);

        foreach ($config['gregwar'] as $key => $value) {
            $container->setParameters(
                [
                    'uecode_image.gregwar.' . $key => $value,
                    'gregwar_image.' . $key        => $value
                ]
            );
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.yml');

        if ($container->getParameter('aws.s3')['enabled']) {
            $this->createAwsClient($container->getParameter('aws.s3'), $container);
            $container->setParameter('uecode_image.provider', 's3');
        } else {
            $container->setParameter('uecode_image.provider', 'local');
        }
    }

    /**
     * @param                  $config
     * @param ContainerBuilder $container
     *
     * @return Definition
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function createAwsClient($config, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('uecode_image.provider.aws')) {
            if (!class_exists('Aws\S3\S3Client')) {
                throw new \RuntimeException('You must require "aws/aws-sdk-php" to use the AWS provider.');
            }

            // Validate the config
            if (empty($config['key']) || empty($config['secret'])) {
                throw new \InvalidArgumentException('The `key` and `secret` must be set in your configuration file to use the AWS Provider');
            }

            $aws = new Definition('Aws\S3\S3Client');
            $aws->setFactoryClass('Aws\S3\S3Client');
            $aws->setFactoryMethod('factory');
            $aws->setArguments(
                [
                    [
                        'key'    => $config['key'],
                        'secret' => $config['secret'],
                        'region' => $config['region']
                    ]
                ]
            );

            // Expose Service to container
            $container->setDefinition('uecode_image.provider.aws', $aws)
                ->setPublic(true);
        } else {
            $aws = $container->getDefinition('uecode_image.provider.aws');
        }

        return $aws;
    }
}
