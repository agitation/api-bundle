<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Controller;

/**
 * @Annotation
 *
 * A special type of endpoint class which is responsible for CRUD operations on a given entity
 */
class EntityController extends Controller
{
    /**
     * @var the entity this class refers to.
     */
    public $entity;

    /**
     * @var a prefix for xxx.read and xxx.write capabilities
     *
     * xxx.read is for `get` and `search`, xxx.write is for `create`, `update`,
     * `delete`, `undelete`, `remove`.
     */
    public $cap;

    /**
     * @var bool if the call should allow cross-origin requests. Only available for get/search requests.
     */
    public $crossOrigin;
}
