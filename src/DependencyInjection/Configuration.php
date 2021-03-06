<?php

namespace Uecode\Bundle\ImageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder    =   new TreeBuilder();
        $rootNode       =   $treeBuilder->root('uecode_image');
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('route')->defaultValue('upload')->end()
                ->scalarNode('use_queue')->defaultFalse()->end()
                ->scalarNode('upload_dir')->defaultValue('web/uploads')->end()
                ->scalarNode('tmp_dir')->defaultValue('web/uploads/tmp')->end()
                ->append($this->getS3Node())
            ->end();

        return $treeBuilder;
    }

    private function getS3Node()
    {
        $treeBuilder    = new TreeBuilder();
        $node           = $treeBuilder->root('aws');

        $node
            ->children()
                ->arrayNode('s3')
                    ->children()
                        ->scalarNode('key')->end()
                        ->scalarNode('secret')->end()
                        ->scalarNode('region')->defaultValue('us-east-1')->end()
                        ->scalarNode('bucket')->defaultFalse()->end()
                        ->scalarNode('directory')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
 }
