<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory = $container->get(PoolFactory::class);
    }

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     * @return ConnectionInterface
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $connections = [];
        if (Context::has('databases')) {
            $connections = Context::get('databases');
        }

        if (isset($connections[$name])) {
            return $connections[$name];
        }

        $pool = $this->factory->getDbPool($name);

        $connection = $pool->get()->getConnection();
        $connections[$name] = $connection;
        Context::set('databases', $connections);

        return $connection;
    }

    /**
     * Add a connection to the resolver.
     *
     * @param  string $name
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return void
     */
    public function addConnection($name, ConnectionInterface $connection)
    {
        $this->connections[$name] = $connection;
    }

    /**
     * Check if a connection has been registered.
     *
     * @param  string $name
     * @return bool
     */
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }
}