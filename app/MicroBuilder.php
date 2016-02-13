<?php

namespace App;

use Phalcon\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Config;

/**
 * @author Caio Almeida <caioamd@hotmail.com>
 */
class MicroBuilder
{
    /**
     * @var Phalcon\DiInterface
     */
    protected $di;

    /**
     * @var Phalcon\Mvc\Micro
     */
    protected $app;

    /**
     * @var Phalcon\Config
     */
    protected $config;

    /**
     * @param  DiInterface $di
     * @return MicroBuilder
     */
    public function __construct(DiInterface $di = null)
    {
        $this->di = $di;

        if ($this->di === null) {
            $this->di = new FactoryDefault();
        }

        $this->app = new Micro();
        $this->defineConfigEnvironment();
    }

    /**
     * based on variable environment APPLICATION_ENV overrides the development configuration with production
     */
    protected function defineConfigEnvironment()
    {
        $production = require __DIR__ . '/../config/production.php';
        $this->config = new Config($production);

        if (getenv('APPLICATION_ENV') != 'production') {
            $development = require __DIR__ . '/../config/development.php';
            $this->config->merge(new Config($development));
        }

        $this->di->set('config', $this->config);
    }

    /**
     * @return MicroBuilder
     */
    public function withExceptionHandler()
    {
        $app = $this->app;
        $this->app->error(function ($exception) use ($app){
            // do something with the exception
            throw $exception;
        });

        return $this;
    }

    public function withRoutes()
    {
        $routes = require __DIR__ . '/../config/routes.php';

        foreach($routes as $routeName => $route) {
            $this->app->{$route['method']}($route['pattern'], [$route['controller'], $route['action'] . 'Action']);
        }

        return $this;
    }

    /**
     * @return MicroBuilder
     */
    public function withServices()
    {
        $services = require __DIR__ . '/../config/services.php';

        foreach($services as $serviceName => $callback) {
            $this->di->set($serviceName, $callback($this->di));
        }
        return $this;
    }

    /**
     * @return MicroBuilder
     */
    public function withNotFoundHandler()
    {
        $app = $this->app;
        $app->notFound(function () use ($app) {
            $app->response->setStatusCode(404, "Not Found")->sendHeaders();
            echo 'this page was not found!';
        });

        return $this;
    }

    /**
     * @return MicroBuilder
     */
    public function withConnections()
    {
        $connections = $this->config->connections;

        foreach ($connections as $connectionName => $connection) {
            $connectionAdapter = $this->createLazyLoadingConnection($connection);
            $this->di->setShared($connectionName, $connectionAdapter);
        }

        return $this;
    }

    private function createLazyLoadingConnection($connection)
    {
        return $connectionAdapter = function () use ($connection) {
            return new $connection->adapter([
                'host'     => $connection->host,
                'username' => $connection->username,
                'password' => $connection->password,
                'dbname'   => $connection->dbname
            ]);
        };
    }

    /**
     * @return Phalcon\Mvc\Micro
     */
    public function getMicro()
    {
        $this->app->setDI($this->di);
        return $this->app;
    }
}
