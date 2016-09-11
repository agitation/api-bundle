<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Common\RequestObjectInterface;
use Agit\ApiBundle\Exception\InvalidObjectException;
use Agit\BaseBundle\Exception\InternalErrorException;
use Agit\IntlBundle\Tool\Translate;

class RequestService extends AbstractObjectService
{
    public function createRequestObject($expectedObject, $rawRequest)
    {
        $result = null;

        if (substr($expectedObject, -2) === "[]") {
            if (! is_array($rawRequest)) {
                throw new InvalidObjectException(Translate::t("The request is expected to be an array."));
            }

            $result = [];

            foreach ($rawRequest as $rawRequestElem) {
                $result[] = $this->createRequestObject(substr($expectedObject, 0, -2), $rawRequestElem);
            }
        } else {
            $meta = $this->objectMetaService->getObjectMetas($expectedObject);
            $expectsScalar = $meta->get("Object")->get("scalar");

            if ($expectsScalar) {
                if (! is_scalar($rawRequest)) {
                    throw new InvalidObjectException(Translate::t("The request is expected to be a scalar value."));
                }

                // we fill the scalar object, but only to see if it passes validation.
                // then we return the bare request
                $object = $this->createObject($expectedObject);

                $object->set("_", $rawRequest);
                $object->validate();

                $result = $rawRequest;
            } else {
                $result = $this->createObject($expectedObject);

                if (is_object($rawRequest)) {
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

        if (! ($object instanceof RequestObjectInterface)) {
            throw new InternalErrorException("Object $objectName must implement RequestObjectInterface.");
        }

        return $object;
    }
}
