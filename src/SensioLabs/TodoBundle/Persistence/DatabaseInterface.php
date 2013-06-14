<?php

namespace SensioLabs\TodoBundle\Persistence;

interface DatabaseInterface
{
    public function select($table, array $columns = array(), array $where = array());

    public function selectOne($table, array $columns = array(), array $where = array(), array $options = array());

    public function insert($table, array $data);

    public function delete($table, $pk, $pkName = 'id');

    public function update($table, array $data, $pk, $pkName = 'id');

    public function fetchColumn($query, $column = 0);

    public function fetchOne($query);

    public function exec($query);

    public function query($query);

    public function quote($data);

    public function getAffectedRows();
}