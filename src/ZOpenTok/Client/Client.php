<?php

namespace ZOpenTok\Client;

use Zend\Http\Client as ZClient;
use Zend\Http\Request as ZRequest;
use Zend\Http\Response;
use ZOpenTok\Client\Response as OTReponse;

use OpenTok\Exception\DomainException;
use OpenTok\Exception\UnexpectedValueException;
use OpenTok\Exception\ArchiveDomainException;
use OpenTok\Exception\ArchiveUnexpectedValueException;
use OpenTok\Exception\AuthenticationException;
use OpenTok\MediaMode;
use OpenTok\Exception\ArchiveAuthenticationException;

class Client extends \OpenTok\Util\Client
{
    protected $apiKey;
    protected $apiSecret;
    protected $apiUrl;
    protected $configured = false;
    protected $client;
    protected $request;

    public function __construct($options = array())
    {
        if (isset($options['adapter']) && is_array($options['adapter'])) {
            $this->getClient()->setOptions($options['adapter']);
        }
        if (isset($options['response']) && $options['response'] instanceof Response) {
            $this->getClient()->setResponse($options['response']);
        } else {
            $this->getClient()->setResponse(new OTReponse());
        }
    }

    public function configure($apiKey, $apiSecret, $apiUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->apiUrl = $apiUrl;
        $this->getClient()->getRequest()->setUri($this->apiUrl);
        $this->getClient()->getRequest()->getHeaders()->addHeaderLine('X-TB-PARTNER-AUTH', $this->apiKey.':'.$this->apiSecret);
        $this->getClient()->setOptions(array(
                'useragent' => OPENTOK_SDK_USER_AGENT
        ));

        $this->configured = true;
    }

    public function isConfigured()
    {
        return $this->configured;
    }

    // General API Requests

    public function createSession($options)
    {
        $this->getClient()->getUri()->setPath('/session/create');
        $this->getClient()->setMethod(ZRequest::METHOD_POST);
        $this->getClient()->setParameterPost($this->postFieldsForOptions($options));

        $response = $this->getClient()->send();
        if (!$response->isSuccess()) {

            $this->handleException($response);
        }

        return  $response->getXmlBody();
    }

    // Archiving API Requests

    public function startArchive($params)
    {
        // set up the request
        $this->getClient()->getUri()->setPath('/v2/partner/'.$this->apiKey.'/archive');
        $this->getClient()->setMethod(ZRequest::METHOD_POST);
        $this->getClient()->getRequest()->setContent(json_encode($params));
        $this->getClient()->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $response = $this->getClient()->send();

        if (!$response->isSuccess()) {
            $this->handleArchiveException($response);
        }
        
        return $response->getArrayBody();
    }

    public function stopArchive($archiveId)
    {
        // set up the request
        $this->getClient()->getUri()->setPath('/v2/partner/'.$this->apiKey.'/archive/'.$archiveId.'/stop');
        $this->getClient()->setMethod(ZRequest::METHOD_POST);
        $this->getClient()->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $response = $this->getClient()->send();
        if (!$response->isSuccess()) {
            $this->handleArchiveException($response);
        }

        return $response->getArrayBody();
    }

    public function getArchive($archiveId)
    {
        $this->getClient()->getUri()->setPath('/v2/partner/'.$this->apiKey.'/archive/'.$archiveId);
        $this->getClient()->setMethod(ZRequest::METHOD_GET);

        $response = $this->getClient()->send();
        if (!$response->isSuccess()) {
            $this->handleException($response);
        }

        return $response->getArrayBody();
    }

    public function deleteArchive($archiveId)
    {
        $this->getClient()->getUri()->setPath('/v2/partner/'.$this->apiKey.'/archive/'.$archiveId);
        $this->getClient()->setMethod(ZRequest::METHOD_DELETE);
        $this->getClient()->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $response = $this->getClient()->send();
        if (!$response->isSuccess()) {
            $this->handleException($response);
        }

        return true;
    }

    public function listArchives($offset, $count)
    {
        $this->getClient()->getUri()->setPath('/v2/partner/'.$this->apiKey.'/archive');
        $this->getClient()->setMethod(ZRequest::METHOD_GET);
        $this->getClient()->getRequest()->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        if ($offset != 0) {
            $this->getClient()->setParameterGet(array('offset', $offset));
        }
        if (!empty($count)) {
            $this->getClient()->setParameterGet(array('count', $count));
        }

        $response = $this->getClient()->send();
        if (!$response->isSuccess()) {
            $this->handleException($response);
        }

        return $response->getArrayBody();
    }

    // Helpers
    private function postFieldsForOptions($options)
    {
        $options['p2p.preference'] = empty($options['mediaMode']) ? MediaMode::ROUTED : $options['mediaMode'];
        unset($options['mediaMode']);
        if (empty($options['location'])) {
            unset($options['location']);
        }
        $options['api_key'] = $this->apiKey;

        return $options;
    }

    private function handleException(Response $resp)
    {
        // TODO: test coverage
        if ($resp->isClientError()) {
            if ($resp->getStatusCode() === 403) {
                throw new AuthenticationException(
                        $this->apiKey,
                        $this->apiSecret,
                        null
                );
            } else {
                throw new DomainException(
                        'The OpenTok API request failed: '. $resp->getReasonPhrase(),
                        null
                );
            }
        } elseif ($resp->isServerError()) {
            throw new UnexpectedValueException(
                    'The OpenTok API server responded with an error: ' . $resp->getReasonPhrase(),
                    null
            );
        } else {
            throw new \Exception('An unexpected error occurred:' . $resp->getReasonPhrase());
        }
    }

    private function handleArchiveException(Response $resp)
    {
        try {
            $this->handleException($resp);
        } catch (AuthenticationException $ae) {
            throw new ArchiveAuthenticationException($this->apiKey, $this->apiSecret, null, $ae->getPrevious());
        } catch (DomainException $de) {
            throw new ArchiveDomainException($de->getMessage(), null, $de->getPrevious());
        } catch (UnexpectedValueException $uve) {
            throw new ArchiveUnexpectedValueException($uve->getMessage(), null, $uve->getPrevious());
        } catch (\Exception $oe) {
            throw new \Exception($oe->getMessage(), null, $oe->getPrevious());
        }
    }

    /**
	 *
	 * @return \Zend\Http\Client
	 */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new ZClient();
        }

        return $this->client;
    }
}
