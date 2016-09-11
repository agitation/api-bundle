<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Endpoint;

/**
 * @Annotation
 *
 * A special type of endpoint class which is responsible for CRUD operations on a given entity
 */
class EntityController extends Controller
{
    /**
     * @var the entity this class refers to
     */
    public $entity;

    /**
     * @var the endpoints this class offers. One or more of "get", "create", "update", "delete", "search".
     */
    public $endpoints;

    /**
     * @var a prefix for xxx.read and xxx.write capabilities
     *
     * xxx.read is for "get" and "search", xxx.write is for "create", "update", "delete"
     */
    public $cap;

    /**
     * @var bool if the call should allow cross-origin requests. Only available for get/search requests.
     */
    public $crossOrigin;
}
