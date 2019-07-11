<?php

namespace PublicFunction\Toolkit\Core;

use PublicFunction\Toolkit\Setup\RestAPI;

abstract class RunableAbstract
{
    /**
     * @var Container
     */
    protected $container;

    public function __construct(Container &$c)
    {
        $this->container = $c;
    }

    /**
     * @return Loader
     */
    public function loader()
    {
        return $this->container->get('loader');
    }

    /**
     * @return RestAPI
     */
    public function rest_api()
    {
        return $this->container->get('rest_api');
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get($name = '')
    {
        return $this->container->get($name);
    }

    abstract public function run();
}
