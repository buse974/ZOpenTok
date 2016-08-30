<?php
namespace ZOpenTok\Service;

use OpenTok\MediaMode;
use OpenTok\Role;

class OpenTok
{

    protected $options;

    protected $opentok;

    protected $session;

    public function __construct(\OpenTok\OpenTok $opentok, $options)
    {
        $this->opentok = $opentok;
        $this->options = $options;
    }

    public function createSession($media_mode = MediaMode::ROUTED)
    {
        $this->session = $this->opentok->createSession(array('mediaMode' => $media_mode));
        
        return $this->session;
    }

    public function createToken($session_id = null, $data = null, $role = \OpenTok\Role::PUBLISHER)
    {
        if (null === $session_id) {
            $session_id = $this->getSessionId();
        }
        
        return $this->opentok->generateToken($session_id, array('role' => $role,'expireTime' => time() + $this->options['expire_time'],'data' => $data));
    }

    public function getSessionId($media_mode = MediaMode::ROUTED)
    {
        return $this->getSession($media_mode)->getSessionId();
    }

    public function getArchive($archiveId)
    {
        $obj_archive = $this->opentok->getArchive($archiveId);
        
        return $obj_archive->toJson();
    }

    public function startArchive($sessionId)
    {
        $obj_archive = $this->opentok->startArchive($sessionId);
        
        return $obj_archive->toJson();
    }

    public function stopArchive($archiveId)
    {
        $obj_archive = $this->opentok->stopArchive($archiveId);
        
        return $obj_archive->toArray();
    }

    protected function getSession($media_mode = MediaMode::ROUTED)
    {
        if (null === $this->session) {
            $this->session = $this->createSession($media_mode);
        }
        
        return $this->session;
    }
}
