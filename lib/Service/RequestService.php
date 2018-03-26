<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Api\Object\RequestObjectInterface;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\BaseBundle\Exception\InternalErrorException;

class RequestService extends AbstractObjectService implements SimpleTypesInterface
{
    public function createRequestObject($expectedObject, $rawRequest)
    {
        $result = null;

        if (substr($expectedObject, -2) === '[]')
        {
            if (! is_array($rawRequest))
            {
                throw new InvalidObjectException('The request is expected to be an array.');
            }

            $result = [];

            foreach ($rawRequest as $rawRequestElem)
            {
                $result[] = $this->createRequestObject(substr($expectedObject, 0, -2), $rawRequestElem);
            }
        }
        else
        {
            if (in_array($expectedObject, self::SIMPLE_TYPES))
            {
                if ($expectedObject === 'string' && !is_string($rawRequest))
                {
                    throw new InvalidObjectException('The request is expected to be a string value.');
                }

                if ($expectedObject === 'integer' && !is_int($rawRequest))
                {
                    throw new InvalidObjectException('The request is expected to be a integer value.');
                }

                if ($expectedObject === 'boolean' && !is_bool($rawRequest))
                {
                    throw new InvalidObjectException('The request is expected to be a boolean value.');
                }

                if ($expectedObject === 'null' && $rawRequest !== null)
                {
                    throw new InvalidObjectException('The request is expected to be null.');
                }

                $result = $rawRequest;
            }
            else
            {
                $result = $this->createObject($expectedObject);

                if (is_object($rawRequest))
                {
                    $this->fill($result, $rawRequest);
                }

                $result->validate();
            }
        }

        return $result;
    }

    private function createObject($objectName)
    {
        $object = $this->objectMetaService->createObject($objectName);

        if (! ($object instanceof RequestObjectInterface))
        {
            throw new InternalErrorException("Object $objectName must implement RequestObjectInterface.");
        }

        return $object;
    }
}
