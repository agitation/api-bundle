<?php

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Annotation\Formatter;

use Agit\BaseBundle\Pluggable\PluginInterface;

/**
 * @Annotation
 */
class Formatter extends AbstractFormatterMeta implements PluginInterface
{
    /**
     * @var The MIME type of documents produced by the formatter
     */
    protected $mimeType;

    /**
     * @var format name, also file extension
     */
    protected $format;

    /**
     * @var service dependencies
     */
    protected $depends = [];
}
