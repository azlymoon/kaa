<?php

namespace Kaa\HttpFoundation;

class HeaderValues
{
    /** @var string[] */
    private $values;

    /** @param string[] $values*/
    final public function __construct($values)
    {
        $this->values = $values;
    }

    /** @return string[] */
    final public function getValues()
    {
        return $this->values;
    }
}
