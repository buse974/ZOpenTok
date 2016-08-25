<?php

namespace ZOpenTok;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use ZOpenTok\Client\Client;
use ZOpenTok\Service\OpenTok;

class Module implements ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                        __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    ],
            ],
        ];
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'aliases' => [
                 'opentok.service' => ZOpenTok\Service\OpenTok::class,
            ],
            'factories' => [
                ZOpenTok\Service\OpenTok::class => function ($sm) {
                    $options = $sm->get('config')['zopentok-conf'];
                    $client = new Client(['adapter' => $sm->get('config')[$options['adapter']]]);
                    $opentok = new \OpenTok\OpenTok($options['api_key'], $options['api_secret'], ['client' => $client]);
                     
                    return new OpenTok($opentok, $options);
                }
            ],
        ];
    }
}
