<?php

namespace MarekBaron\Test\Container;

use stdClass;

class DummyFactory
{
    public function __invoke($container, $id): stdClass
    {
        return new stdClass();
    }
}
