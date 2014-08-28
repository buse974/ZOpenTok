<?php

namespace ZOpenTok;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use OpenTok\OpenTok as LibOpenTok;
use ZOpenTok\Client\Client;

class Module implements ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
                'Zend\Loader\StandardAutoloader' => array(
                    'namespaces' => array(
                            __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                        ),
                ),
                  'Zend\Loader\ClassMapAutoloader' => array(
                       __DIR__ . '/autoload_classmap.php',
                ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                 'opentok.service'    => 'ZOpenTok\Service\OpenTok',
            ),
            'invokables' => array(
                'ZOpenTok\Service\OpenTok'   => 'ZOpenTok\Service\OpenTok',
            ),
        	'factories' => array(
            	'opentok' => function($sm) {
            		$zopentok = $sm->get('config')['zopentok-conf'];
            		$client = new Client(array('adapter' => $sm->get('config')[$zopentok['adapter']]));
            		
            		return new LibOpenTok($zopentok['api_key'], $zopentok['api_secret'], array('client' => $client));
            	}
            ),
        );
    }
}
