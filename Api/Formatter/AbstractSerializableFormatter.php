<?php
/**
 * @package    agitation/api
 * @link       http://github.com/agitation/AgitApiBundle
 * @author     Alex Günsche <http://www.agitsol.com/>
 * @copyright  2012-2015 AGITsol GmbH
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Api\Formatter;

use Agit\CoreBundle\Exception\InternalErrorException;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

abstract class AbstractSerializableFormatter extends AbstractFormatter
{
    protected function getHttpHeaders()
    {
        $headers = new ResponseHeaderBag();
        $headers->set('Content-Type', static::$mimeType);

        return $headers;
    }

    protected function getHttpContent()
    {
        $Response = $this->createContent();
        $this->compactEntities($Response->getPayload());
        $Response->setPayload($this->getCompactPayload());
        $Response->setEntityList($this->getCompactEntityList());
        return $this->getEncoder()->encode($Response, static::$format);
    }

    private function createContent()
    {
        $Response = $this->Container->get('agit.api.object')->createObject('common.v1/Response');
        $Response->set('success', $this->Endpoint->getSuccess());

        foreach ($this->Endpoint->getMessages() as $Message)
            $Response->add('MessageList', $Message);

        if ($this->Endpoint->getSuccess())
            $Response->set('payload', $this->Endpoint->getResponse());

        return $Response;
    }

    abstract protected function getEncoder();

    // functions for compact mode

    private $idx;

    private $keyPrefix = '#e#';

    private $compactPayload;

    private $compactEntityList = array();

    /**
     * {@inheritdoc}
     */
    public function compactEntities($payload)
    {
        $this->idx = 0;
        $this->compactEntityList = array();
        $this->compactPayload = $this->processValue($payload);
    }

    public function getCompactPayload()
    {
        return $this->compactPayload;
    }

    public function getCompactEntityList()
    {
        $compactEntityList = array();

        foreach ($this->compactEntityList as $compactEntity)
            $compactEntityList[$compactEntity['idx']] = $compactEntity['obj'];

        return $compactEntityList;
    }

    public function processValue($value)
    {
        $processed = null;

        if (is_scalar($value))
        {
            $processed = $value;
        }
        elseif (is_array($value))
        {
            $processed = array();
            foreach ($value as $k=>$v)
                $processed[$k] = $this->processValue($v);
        }
        elseif (is_object($value))
        {
            if ($this->isEntityObject($value))
            {
                $processed = $this->addEntityObject($value);
            }
            else
            {
                $processed = new \stdClass();
                foreach (get_object_vars($value) as $k=>$v)
                    $processed->$k = $this->processValue($v);
            }
        }

        return $processed;
    }

    private function isEntityObject($value)
    {
        return (is_callable(array($value, 'hasProperty')) &&
            $value->hasProperty('id') &&
            $value->get('id'));
    }

    private function addEntityObject($Object)
    {
        $key = sprintf('%s:%s', $Object->getObjectName(), $Object->get('id'));

        if (!isset($this->compactEntityList[$key]))
        {
            $this->compactEntityList[$key] = array('idx'=>sprintf("%s:%s", $this->keyPrefix, $this->idx++), 'obj'=>new \stdClass());

            foreach ($Object->getValues() as $k => $v)
                $this->compactEntityList[$key]['obj']->$k = $this->processValue($v);
        }

        return $this->compactEntityList[$key]['idx'];
    }
}
