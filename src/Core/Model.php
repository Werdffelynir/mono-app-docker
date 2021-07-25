<?php

namespace Lib\Core;

class Model
{
    // use Create;
    private DB $db;

    public static bool $FETCH_OBJ = true;

    protected string $table = '';
    protected array $lastQuery = ['sql' => '', 'bind' => [], 'errors' => []];

    public array $filter = [];
    public array $required = [];

    public function __construct()
    {
        /** @var DB $db */
        $db = Core::container(DB::class);
        $this->db = $db;
    }

    public function db()
    {
        return $this->db;
    }

    public function pdo()
    {
        return $this->db->pdo();
    }

    /**
     * Attempt to complete the request.
     * The method determines the type of operation to perform the corresponding task.
     * <pre>
     * The examples below are identical, they will return the id of the added record if the database driver allows
     * ->executeQuery('INSERT INTO folks (name, addr, city) values (?, ?, ?)' ['Mr Doctor', 'some street', 'London']);
     * ->executeQuery('INSERT INTO folks (name, addr, city) values (:name, :addr, :city)' [':name'=>'Mr Doctor', ':addr'=>'some street', ':city'=>'London']);
     *
     * The examples below will return a single entry
     * ->executeQuery('SELECT name, addr, city FROM folks WHERE city = :city', [':city'=>'London'], false);
     * </pre>
     *
     * @param string $sql Request with placeholders
     * @param null $bind Array of values for binding placeholders
     * @param bool $fetchAll Bool kay, select all or one entry
     * @return array|false|int|mixed|string     Depending on the type of request and attributes \PDO
     */
    public function executeQuery(string $sql, $bind = null, $fetchAll = true)
    {
        $this->clear();
        $this->lastQuery['sql'] = trim($sql);
        $this->lastQuery['bind'] = empty($bind) ? null : (array)$bind;

        try {
            $pdoStmt = $this->db->prepare($this->lastQuery['sql']);
            if ($pdoStmt->execute($this->lastQuery['bind']) !== false) {
                $first = strtolower(str_word_count($sql, 1)[0]);
                switch ($first) {
                    case 'select':
                    case 'pragma':
                    case 'describe':
                        if ($fetchAll)
                            return $pdoStmt->fetchAll(self::$FETCH_OBJ ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC);
                        else
                            return $pdoStmt->fetch(self::$FETCH_OBJ ? \PDO::FETCH_OBJ : \PDO::FETCH_ASSOC);
                    case 'insert':
                        return $this->db->pdo()->lastInsertId();
                    case 'update':
                    case 'delete':
                        return $pdoStmt->rowCount();
                    default:
                        return false;
                }
            }
        } catch (\PDOException $e) {
            $this->lastQuery['errors'] = $e->getMessage();
            return false;
        }
    }


    /**
     * Attempting to execute a single row return query
     *
     * @param string $sql
     * @param null $bind
     * @return array|bool|int|object
     */
    public function executeOne(string $sql, $bind = null)
    {
        return $this->executeQuery($sql, $bind, $fetchAll = false);
    }


    /**
     * Attempting to execute a query that returns multiple rows
     *
     * @param string $sql
     * @param null $bind
     * @return array|bool|int|object
     */
    public function executeAll(string $sql, $bind = null)
    {
        return $this->executeQuery($sql, $bind, $fetchAll = true);
    }

    /**
     * Simplified data retrieval request
     *
     * <pre>
     * ->select('id, link, title','active=?', [1]);
     * ->select('id, link, title',['active' => 1]', [1]);
     * </pre>
     *
     * @param string $fields
     * @param array|string $where
     * @param null $bind
     * @param bool $fetchAll
     * @return array|bool|int|object
     */
    public function select(string $fields, $where, $bind = null, $fetchAll = true)
    {
        $fields = is_array($fields) ? $fields : explode(',', $fields);
        $sql = "SELECT " . join(',', $fields) . " FROM " . $this->table;

        if (!empty($where)) {
            if (is_array($where)) {
                $whereConvert = '';
                foreach ($where as $key => $value) {
                    $whereConvert .= empty($whereConvert) ? '' : ' AND ';
                    $whereConvert .= $key . ' = ' . (is_string($value) ? "'$value'" : $value);
                }
                $where = $whereConvert;
            }
            $sql .= " WHERE " . $where;
        }
        $sql .= ";";

        return $this->executeQuery($sql, $bind, $fetchAll);
    }

    /**
     * @param string $fields
     * @param $where
     * @param null $bind
     * @return array|bool|int|object|string
     */
    public function selectOne(string $fields, $where, $bind = null)
    {
        return $this->select($fields, $where, $bind, false);
    }


    /**
     * Simplified data write request
     *
     * <pre>
     * An example will execute the request and will return lastInsertId, if possible.
     * ->insert(['link'=>'some link', 'title'=>'some title']);
     * </pre>
     *
     * @param array $columnData parameters
     * @return int                  returns the number of modified rows
     */
    public function insert(array $columnData)
    {
        $columns = array_keys($columnData);
        $data = array_values($columnData); // $this->columnsTypes($columnData);

        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s);",
            ' `' . $this->table . '` ',
            ' `' . implode('`, `', $columns) . '` ',
            implode(', ', array_fill(0, count($data), '?'))
        );

        return $this->executeQuery($sql, $data);
    }

    public function insertOrUpdate(array $where, array $columnData)
    {
        $select = $this->select('*', $where);

    }


    public function columnsTypes(array $columns)
    {
        foreach ($columns as $key => $column) {
            //
            // Possibles values for the returned string are: "boolean" "integer" "double"
            // (for historical reasons "double" is returned in case of a float, and not simply "float")
            // "string" "array" "object" "resource" "NULL" "unknown type" "resource (closed)" since 7.2.0
            $type = gettype($column);

            switch ($type) {
                case "string":
                    $columns[$key] = "'{$column}'";
                    break;
                case "boolean":
                case "integer":
                    $columns[$key] = (int)$column;
                    break;
                case "float":
                case "double":
                    $columns[$key] = (float)$column;
                    break;
                case "NULL":
                    $columns[$key] = null;
                    break;
                case "array":
                case "object":
                    $columns[$key] = "'" . json_encode($column) . "'";
                    break;
            }
        }
        return $columns;
    }

    /**
     * Simplified data deletion request
     *
     * <pre>
     * An example will execute the request and will return lastInsertId, if possible.
     * ->delete('my_table', 'id = ?', [123]);
     * </pre>
     *
     * @param string $where conditions with placeholders
     * @param null $bind bind array for placeholders
     * @return array|bool|int|object    returns the number of modified rows
     */
    public function delete(string $where, $bind = null)
    {
        $sql = "DELETE FROM " . $this->table . " WHERE " . $where . ";";

        return $this->executeQuery($sql, $bind);
    }

    /**
     * Simplified data update request.
     * Please note that placeholders in this request should only be nameless (WHERE id = ?, title = ?),
     * but if placeholders are called in the request, they will be regenerated into nameless ones, which can lead to
     * unpredictable results.
     * The conversion takes place one after another, and if the positions are different the request will be distorted.
     *
     * <pre>
     * ->update(['link'=>'new link', 'title'=>'new title'], 'id = ?', [123]);
     * // Execute SQL request 'UPDATE my_table SET ('link'=?, 'title'=?) WHERE id = ?'
     * </pre>
     *
     * @param array $columnData
     * @param string|array $where
     * @param null $bind
     * @return array|bool|int|object
     */
    public function update(array $columnData, $where, $bind = null)
    {
        $columns = array_keys($columnData);
        if (is_array($where)) {
            $whereConvert = '';
            foreach ($where as $key => $value) {
                $whereConvert .= empty($whereConvert) ? '' : ' AND ';
                if (substr($value, 0, 1) === ':') {
                    $value = preg_replace('|:\w+|', '?', $value);
                    $whereConvert .= $key . ' = ' . $value;
                } else {
                    $whereConvert .= $key . ' = ' . (is_string($value) ? "'$value'" : $value);
                }
            }
            $where = $whereConvert;
        } else {
            $where = preg_replace('|:\w+|', '?', $where);
        }

        if (empty($bind))
            $bind = array_values($columnData);
        else
            $bind = array_values(array_merge($columnData, (array)$bind));

        $sql = sprintf("UPDATE %s SET %s WHERE %s;",
            ' `' . $this->table . '` ',
            ' `' . implode('`=?, `', $columns) . '` = ? ',
            $where
        );

        return $this->executeQuery($sql, $bind);
    }


    private function clear()
    {
        $this->lastQuery['errors'] = null;
        $this->lastQuery['bind'] = null;
        $this->lastQuery['sql'] = null;
    }


    /**
     * Error output if any. Can be used to identify errors
     * @param bool|string $row can take params: "error", "sql" or "bind", default false
     * @return array|bool
     */
    public function getError($row = false)
    {
        if (!empty($this->lastQuery['errors'])) {
            $err = [
                'error' => $this->lastQuery['errors'],
                'sql' => $this->lastQuery['sql'],
                'bind' => $this->lastQuery['bind']
            ];
            if (isset($err[$row]))
                return $err[$row];
            return $err;
        } else
            return false;
    }

}