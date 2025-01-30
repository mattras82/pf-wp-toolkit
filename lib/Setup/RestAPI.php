<?php

namespace PublicFunction\Toolkit\Setup;


use PublicFunction\Toolkit\Core\Container;
use PublicFunction\Toolkit\Core\RunableAbstract;
use WP_REST_Server;

class RestAPI extends RunableAbstract
{

    protected $endpoints;

    public function __construct(Container &$c)
    {
        parent::__construct($c);

        $this->endpoints = [];
    }

    public function addEndpoint($route, $options) {
        if (!is_array($options) || !isset($options['callback'])) {
            return false;
        }
        if (!isset($options['methods'])) $options['methods'] = \WP_REST_Server::READABLE;

        $this->endpoints[$route] = $options;

        return true;
    }

    public function registerEndpoints() {
        if (count($this->endpoints) > 0) {
            foreach ($this->endpoints as $route => $options) {
                register_rest_route('pf_rest/v1', $route, $options);
            }
        }
    }

    public function run()
    {
        $this->loader()->addAction('rest_api_init', [$this, 'registerEndpoints']);
    }

}
