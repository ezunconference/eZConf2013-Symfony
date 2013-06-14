<?php

namespace SensioLabs\TodoBundle\Persistence;

class Database implements DatabaseInterface, CrudInterface
{
    /**
     * @var \PDO
     */
    private $dbh;
    private $dsn;
    private $username;
    private $password;
    private $options;
    private $affectedRows;
    private $lastInsertId;

    public function __construct($database, $username, $password, $hostname, $port, array $options = array())
    {
        $this->username = $username;
        $this->password = $password;
        $this->dsn = sprintf('mysql:host=%s;port=%u;dbname=%s', $hostname, (int) $port, $database);
        $this->affectedRows = 0;
    }

    private function connect()
    {
        if (null !== $this->dbh) {
            return;
        }

        try {
            $this->dbh = new \PDO($this->dsn, $this->username, $this->password, $this->options);
        } catch (\PDOException $e) {
            throw new DatabaseException(sprintf('Cannot connect to MySQL server or database with user %s and DSN %s.', $this->username, $this->dsn), $e);
        }
    }

    public function insert($table, array $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);

        $query = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES ('%s')",
            $table,
            implode('`, `', $columns),
            implode(', ', str_split(str_repeat('?', count($values))))
        );

        $this->exec($query, $values);

        $this->lastInsertId = $this->dbh->lastInsertId;

        return $this->lastInsertId;
    }

    public function update($table, array $data, $pk, $pkName = 'id')
    {
        $values = array();
        $params = array();
        foreach ($data as $column => $value) {
            $values[] = sprintf('`%s` = ?', $column, $value);
            $params[] = $value;
        }

        $params[] = $pk;

        $query = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            $table,
            implode(', ', $values),
            $pkName
        );

        return $this->exec($query, $params);
    }

    public function delete($table, $pk, $pkName = 'id')
    {
        $query = sprintf("DELETE FROM `%s` WHERE `%s` = ?", $table, $pkName);

        return $this->exec($query, array($pk));
    }

    public function select($table, array $columns = array(), array $where = array())
    {
        return $this->fetchAll($this->buildQuery($table, $columns, $where), array_values($where));
    }

    public function selectOne($table, array $columns = array(), array $where = array(), array $options = array())
    {
        return $this->fetchOne($this->buildQuery($table, $columns, $where, $options), array_values($where));
    }

    private function buildQuery($table, array $columns = array(), array $where = array(), array $options = array())
    {
        $whereQuery = '';
        if (!empty($where)) {
            $conditions = array();
            foreach ($where as $column => $value) {
                $conditions[] = sprintf("`%s` = ?", $column, $value);
            }
            $whereQuery.= 'WHERE '.implode(' AND ', $conditions);
        }

        if (isset($options['random']) && true === $options['random']) {
            $options['order_by'] = 'RAND()';
        }

        $orderBy = '';
        if (!empty($options['order_by'])) {
            $orderBy = sprintf('ORDER BY %s', $options['order_by']);
        }

        return trim(sprintf(
            'SELECT %s FROM `%s` %s %s',
            empty($columns) ? '*' : implode(', ', $columns),
            $table,
            $whereQuery,
            $orderBy
        ));
    }

    public function fetchColumn($query, $column = 0)
    {
        $row = $this->fetchOne($query);

        if (!is_array($row)) {
            throw new QueryException(sprintf('There are no records matching the SQL query: %s.'.$query));
        }

        if (!isset($row[$column])) {
            throw new DatabaseException(sprintf('There is no column named "%s" in the result set of the last executed query.', $column));
        }

        return $row[$column];
    }

    public function fetchOne($query, array $params = array())
    {
        $stmt = $this->query($query, $params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchAll($query, array $params = array())
    {
        $stmt = $this->query($query, $params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function exec($query, array $params = array())
    {
        $stmt = $this->query($query, $params);

        $this->affectedRows = $stmt->rowCount;
        if (!$this->affectedRows) {
            throw new QueryException(sprintf('Unable to execute query: %s.'.$query));
        }

        return $this->affectedRows;
    }

    public function query($query, array $params = array())
    {
        $this->affectedRows = 0;

        $this->connect();
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($params);
        } catch (\PDOException $e) {
            throw new QueryException('SQL query execution failed: '.$query);
        }

        return $stmt;
    }

    public function quote($data)
    {
        if (is_numeric($data)) {
            return $data;
        }

        $this->connect();

        return $this->dbh->quote($data);
    }

    public function getAffectedRows()
    {
        return $this->affectedRows;
    }
}