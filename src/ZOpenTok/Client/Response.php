<?php

namespace ZOpenTok\Client;

use Zend\Http\Response as BaseResponse;

class Response extends BaseResponse
{
	public function getXmlBody()
	{
		$errorMessage = null;
		$internalErrors = libxml_use_internal_errors(true);
		$disableEntities = libxml_disable_entity_loader(true);
		libxml_clear_errors();
		
		try {
			$xml = new \SimpleXMLElement((string) $this->getBody() ?: '<root />', LIBXML_NONET);
			if ($error = libxml_get_last_error()) {
				$errorMessage = $error->message;
			}
		} catch (\Exception $e) {
			$errorMessage = $e->getMessage();
		}
		
		libxml_clear_errors();
		libxml_use_internal_errors($internalErrors);
		libxml_disable_entity_loader($disableEntities);
		
		if ($errorMessage) {
			throw new RuntimeException('Unable to parse response body into XML: ' . $errorMessage);
		}
		
		return $xml;
	}
}