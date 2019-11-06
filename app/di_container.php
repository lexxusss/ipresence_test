<?php

use DI\Container;


/**
 * @throws Exception
 */
function getContainer() {
    return DI_Container_Builder::getContainer();
}

/**
 * Class DI_Container_Builder
 */
final class DI_Container_Builder
{
    /**
     * @var Container
     */
    private static $container;

    /**
     * @return Container
     * @throws Exception
     */
    public static function getContainer()
    {
        if (static::$container === null) {
            static::$container = new static();
        }

        return (new DI\ContainerBuilder())->build();
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}
