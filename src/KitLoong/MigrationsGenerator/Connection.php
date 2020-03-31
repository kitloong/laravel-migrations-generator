<?php
/**
 * Created by PhpStorm.
 * User: liow.kitloong
 * Date: 2020/03/31
 * Time: 22:55
 */

namespace KitLoong\MigrationsGenerator;

class Connection
{
    /**
     * @var string
     */
    private $connection;

    public function __construct(string $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * @param  string  $connection
     */
    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }
}
