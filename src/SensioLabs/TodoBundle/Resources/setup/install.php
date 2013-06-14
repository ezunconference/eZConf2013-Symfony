<?php

$database = 'dpc2013';

$dbh = new \PDO("mysql:host=localhost;port=3306", 'root', '', array(
    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
));

$dbh->exec("CREATE DATABASE IF NOT EXISTS `$database`;");
$dbh->query("USE `$database`;");
$dbh->exec("DROP TABLE IF EXISTS `todo`;");

$query  = 'CREATE TABLE IF NOT EXISTS `todo` ('."\n";
$query .= '`id` INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY,';
$query .= '`title` VARCHAR(100),'."\n";
$query .= '`is_done` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0);';

$dbh->exec($query);

$data = array(
  array('title' => 'Do the dishes', 'is_done' => 1),
  array('title' => 'Read a book', 'is_done' => 0),
  array('title' => 'Do the homework', 'is_done' => 0),
  array('title' => 'Cook some cakes for birthday', 'is_done' => 1),
);

foreach ($data as $todo) {
    $stmt = $dbh->prepare("INSERT INTO `todo` (`title`, `is_done`) VALUES (?, ?)");
    $stmt->bindValue(1, $todo['title'], \PDO::PARAM_STR);
    $stmt->bindValue(2, $todo['is_done'], \PDO::PARAM_INT);
    $stmt->execute();
}

echo 'Installation done!';
echo "\n";
