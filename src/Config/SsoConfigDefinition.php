<?php

namespace App\Config;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SsoConfigDefinition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sso');

        $rootNode
            ->children()
                ->booleanNode('disable_registration_route')
                    ->defaultFalse()
                ->end() // disable_registration_route
                ->scalarNode('registration_url')
                    ->defaultNull()
                ->end() // registration_url
                ->scalarNode('logout_redirect_url')
                    ->defaultNull()
                ->end() // logout_redirect_url
            ->end()
        ;

        return $treeBuilder;
    }
}
