ZOpenTok
========

Zend Module for OpenTok

# Install

###Params###

```php
array(
        'zopentok-conf' => array(
           /**
        	* api key opentok
        	*/
        	'api_key'     =>  'xxxxxxxx',
        	
           /**
        	* api secret opentok
        	*/
        	'api_secret'  =>  'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        	'expire_time' => <number>,
           /**
        	* Adapter Http Client
        	*
        	*/
        	'adapter'     => 'http-adapter',
        ),
        'http-adapter' => array(
		    'adapter'    => 'Zend\Http\Client\Adapter\Proxy',
		    'proxy_host' => 'proxy.name.com',
		    'proxy_port' =>  8000,
		    'proxy_user' => 'buse974',
		    'proxy_pass' => 'zopentok'
))
```


	Add this configuration in ./config/autoload directory from your project.


# Samples

```php
$m_zopentok = $this->getServiceLocator()->get('opentok.service');
$session = $m_zopentok->createSession();
$token = $m_zopentok->createToken();
$session_id = $m_zopentok->getSessionId();

```


