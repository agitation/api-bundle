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
        $response = $this->createContent();
        $this->compactEntities($response->getPayload());
        $response->setPayload($this->getCompactPayload());
        $response->setEntityList($this->getCompactEntityList());
        return $this->getEncoder()->encode($response, static::$format);
    }

    private function createContent()
    {
        $response = $this->container->get('agit.api.object')->createObject('common.v1/Response');
        $response->set('success', $this->endpoint->getSuccess());

        foreach ($this->endpoint->getMessages() as $message)
            $response->add('MessageList', $message);

        if ($this->endpoint->getSuccess())
            $response->set('payload', $this->endpoint->getResponse());

        return $response;
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

    private function addEntityObject($object)
    {
        $key = sprintf('%s:%s', $object->getObjectName(), $object->get('id'));

        if (!isset($this->compactEntityList[$key]))
        {
            $this->compactEntityList[$key] = array('idx'=>sprintf("%s:%s", $this->keyPrefix, $this->idx++), 'obj'=>new \stdClass());

            foreach ($object->getValues() as $k => $v)
                $this->compactEntityList[$key]['obj']->$k = $this->processValue($v);
        }

        return $this->compactEntityList[$key]['idx'];
    }
}
