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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Christopher A. Moore <chris.a.moore@gmail.com>
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('uecode_image');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('route')
                    ->defaultValue('upload')
                ->end()
                ->scalarNode('use_queue')
                    ->defaultFalse()
                ->end()
                ->scalarNode('upload_dir')
                    ->defaultValue('web/uploads')
                ->end()
                ->scalarNode('tmp_dir')
                    ->defaultValue('web/uploads/tmp')
                ->end()
                ->booleanNode('throw_exception')
                    ->defaultFalse()
                ->end()
                ->scalarNode('fallback_image')
                    ->defaultValue(null)
                ->end()
                ->append($this->getS3Node())
            ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    private function getS3Node()
    {
        $treeBuilder = new TreeBuilder();
        $node        = $treeBuilder->root('aws');

        $node
            ->children()
                ->arrayNode('s3')->canBeDisabled()
                    ->children()
                        ->scalarNode('key')->end()
                        ->scalarNode('secret')->end()
                        ->scalarNode('region')
                            ->defaultValue('us-east-1')
                        ->end()
                        ->scalarNode('bucket')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('directory')
                            ->defaultFalse()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $node;
    }
 }
