<?php

namespace SensioLabs\TodoBundle\Persistence;

interface CrudInterface
{
    public function select($table, array $columns = array(), array $where = array());

    public function selectOne($table, array $columns = array(), array $where = array());

    public function insert($table, array $data);

    public function delete($table, $pk, $pkName = 'id');

    public function update($table, array $data, $pk, $pkName = 'id');
}