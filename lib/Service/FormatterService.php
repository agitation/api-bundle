<?php
declare(strict_types=1);

/*
 * @package    agitation/api-bundle
 * @link       http://github.com/agitation/api-bundle
 * @author     Alexander Günsche
 * @license    http://opensource.org/licenses/MIT
 */

namespace Agit\ApiBundle\Service;

use Agit\ApiBundle\Exception\FormatterNotFoundException;

class FormatterService
{
    private $formatters = [];

    public function addFormatter($extension, AbstractFormatter $formatter)
    {
        $this->formatters[$extension] = $formatter;
    }

    public function formatExists($extension)
    {
        return isset($this->formatters[$extension]);
    }

    public function getFormatter($extension)
    {
        if (! $this->formatExists($extension))
        {
            throw new FormatterNotFoundException('The requested format is not supported.');
        }

        return $this->formatters[$extension];
    }
}
