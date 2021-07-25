<?php

namespace Lib\Core;

/**
 * <pre>
 * $db = new DB([
 *       // pgsql, mysql, sqlite
 *      'driver' => '',
 *       // sqlite only
 *      'path' => '/path/to/database.sqlite'
 *      'host' => '',
 *      'port' => '',
 *      'database' => '',
 *      'user' => '',
 *      'password' => '',
 *      'options' => [],
 * ])
 * $db = new DB([
 *      driver' => 'sqlite',
 *      path' => '/path/to/database.sqlite',
 * ])
 *
 * fetch mode:
 *      PDO::ATTR_DEFAULT_FETCH_MODE
 *      PDO::FETCH_BOTH
 *      PDO::FETCH_COLUMN
 *      PDO::FETCH_UNIQUE
 *      PDO::FETCH_GROUP
 *      PDO::FETCH_CLASS
 *      PDO::FETCH_FUNC
 *      PDO::FETCH_ASSOC
 *      PDO::FETCH_NAMED
 *      PDO::FETCH_NUM
 *      PDO::FETCH_OBJ
 * </pre>
 * Class DB
 * @package Lib\Core
 */
class DB
{
    private \PDO $pdo;
    private array $params = [];
    private static ?DB $dbInstance = null;

    /**
     * DB constructor.
     * @param array $params
     * @throws \Exception
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->connection();
    }

    public function __invoke(array $params)
    {
        return new self($params);
    }

    /**
     * @return \PDO
     * @throws \Exception
     */
    private function connection(): \PDO
    {
        $connectString = null;
        $db_user = null;
        $db_pass = null;
        $options = null;

        switch ($this->params['driver']) {
            case 'mysql':
                $connectString = sprintf("mysql:host=%s:%d;dbname=%s",
                    $this->params['host'],
                    $this->params['port'],
                    $this->params['database']
                );
                $db_user = $this->params['user'];
                $db_pass = $this->params['password'];

                if (isset($this->params['options']))
                    $options = $this->params['options'];

                $options = array_merge($options, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ]);

                break;
            case 'pgsql':
                $connectString = sprintf("pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                    $this->params['host'],
                    $this->params['port'],
                    $this->params['database'],
                    $this->params['user'],
                    $this->params['password']
                );

                if (isset($this->params['options'])) $options = $this->params['options'];

                break;
            case 'sqlite':
                $connectString = sprintf("sqlite:%s", trim($this->params['path']));
                break;
        }

        try {
            $this->pdo = new \PDO($connectString, $db_user, $db_pass, $options);
        } catch (\PDOException $err) {
            throw new \Exception('DB CONNECTION FAILED: ' . $err->getCode() . ' // ' .$err->getMessage());
        }

        return $this->pdo;
    }

    /**
     * @return \PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * @param string $statement
     * @return false|\PDOStatement
     */
    public function query(string $statement)
    {
        return $this->pdo->query($statement);
    }

    /**
     * @param string $statement
     * @return false|int
     */
    public function exec(string $statement)
    {
        return $this->pdo->exec($statement);
    }

    /**
     * @param string $statement
     * @param array $driver_options
     * @return bool|\PDOStatement
     */
    public function prepare(string $statement, array $driver_options = [])
    {
        return $this->pdo->prepare($statement, $driver_options);
    }

    /**
     * @return array|false
     */
    public function error()
    {
        return $this->pdo->errorCode() === '00000'
            ? false
            : $this->pdo->errorInfo();
    }

    /**
     * @param array $params
     * @return \PDO
     * @throws \Exception
     */
    public static function pdoStatic(array $params)
    {
        if (!self::$dbInstance) {
            self::$dbInstance = new self($params);
        }

        return self::$dbInstance->pdo;
    }


    /**
     * Returns table information
     *
     * @param string $table
     * @return array
     */
    public function tableInfo(string $table)
    {
        $driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver == 'sqlite') {
            $sql = "PRAGMA table_info('" . $table . "');";
            $key = "name";
        } elseif ($driver == 'mysql') {
            $sql = "DESCRIBE " . $table . ";";
            $key = "Field";
        } else {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
            $key = "column_name";
        }

        if (false !== ($columns = $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC))) {
            return $columns;
        }
        return array();
    }
}



