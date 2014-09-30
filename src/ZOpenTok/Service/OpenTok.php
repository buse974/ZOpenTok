<?php

namespace ZOpenTok\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use OpenTok\MediaMode;
use OpenTok\Role;

class OpenTok implements ServiceManagerAwareInterface
{
    protected $service_manager;
    protected $session;

    public function createSession($media_mode = MediaMode::ROUTED)
    {
        $this->session = $this->getOpenTok()->createSession(array('mediaMode' => $media_mode));

        return $this->session;
    }

    public function createToken($session_id = null,$data = null, $role = \OpenTok\Role::PUBLISHER)
    {
        if (null===$session_id) {
            $session_id = $this->getSessionId();
        }

        return $this->getOpenTok()->generateToken($session_id,array(
                'role' => $role,
                'expireTime' => time() + $this->getServiceManager()->get('config')['zopentok-conf']['expire_time'],
                'data' => $data
        ));
    }
    public function getSessionId()
    {
        return $this->getSession()->getSessionId();
    }

    public function getArchive($archiveId)
    {
        $obj_archive = $this->getOpenTok()->getArchive($archiveId);

        return $obj_archive->toJson();
    }

    public function startArchive($sessionId)
    {
        $obj_archive = $this->getOpenTok()->startArchive($sessionId);

        return $obj_archive->toArray();
    }

    public function stopArchive($archiveId)
    {
        $obj_archive = $this->getOpenTok()->stopArchive($archiveId);

        return $obj_archive->toArray();
    }

    protected function getSession()
    {
        if (null===$this->session) {
            $this->session = $this->createSession();
        }

        return $this->session;
    }

    /**
     * @return \OpenTok\OpenTok
     */
    public function getOpenTok()
    {
        return $this->getServiceManager()->get('opentok');
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->service_manager;
    }

    /**
     * Set service manager
     *
     * @param ServiceManager $serviceManager
     */
    public function setServiceManager(ServiceManager $service_manager)
    {
        $this->service_manager = $service_manager;
    }

}
