<?php

namespace SensioLabs\TodoBundle\Gateway;

use SensioLabs\TodoBundle\Persistence\DatabaseInterface;
use SensioLabs\TodoBundle\Persistence\QueryException;

class TodoGateway
{
    private $database;
    private $table;

    /**
     * Constructor.
     *
     * @param string            $table    The table name
     * @param DatabaseInterface $database The database connection
     */
    public function __construct($table, DatabaseInterface $database)
    {
        $this->database = $database;
        $this->table = $table;
    }

    /**
     * Removes the task identified by its primary key from the database.
     *
     * @param int $id The task primary key
     * @param int The number of affected rows
     * @throws GatewayException
     */
    public function deleteTask($id)
    {
        $rows = $this->database->delete($this->table, $id);
        if (0 === $rows) {
            throw new GatewayException(sprintf('Unable to delete task identified by primary key #%u.', $id));
        }

        return $rows;
    }

    /**
     * Closes the task identified by its primary key.
     *
     * @param int $id The task primary key
     * @param int The number of affected rows
     * @throws GatewayException
     */
    public function closeTask($id)
    {
        try {
            return $this->database->update($this->table, array('is_done' => 1), $id);
        } catch (QueryException $e) {
            throw new GatewayException(sprintf('Unable to close task identified by primary key #%u.', $id));
        }
    }

    /**
     * Creates a new task record.
     *
     * @param string $title The task title
     * @param int The last incremented primary key
     * @throws GatewayException
     * @throws InvalidArgumentException
     */
    public function createTask($title)
    {
        if (empty($title)) {
            throw new \InvalidArgumentException('Title must be filled.');
        }

        try {
            return $this->database->insert($this->table, array('title' => $title));
        } catch (QueryException $e) {
            throw new GatewayException(sprintf('Unable to create new task: "%s".', $id));
        }
    }

    /**
     * Returns the total number of tasks in the database.
     *
     * @param int The number of tasks in the database
     */
    public function countTasks()
    {
        return (int) current($this->database->selectOne($this->table, array('COUNT(*)')));
    }

    /**
     * Fetches a single task identified by its primary key.
     *
     * @param int $id The task primary key
     * @param array The task record
     */
    public function getTask($id)
    {
        return $this->database->selectOne($this->table, array(), array('id' => (int) $id));
    }

    /**
     * Fetches a single random task.
     *
     * @param int $id The task primary key
     * @param array The task record
     */
    public function getRandomTask()
    {
        return $this->database->selectOne($this->table, array(), array(), array('random' => true));
    }

    /**
     * Fetches all tasks from the database.
     *
     * @param array A collection of task records
     */
    public function getAllTasks()
    {
        return $this->database->select($this->table);
    }
}