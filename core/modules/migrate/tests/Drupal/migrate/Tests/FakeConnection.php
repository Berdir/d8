<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeConnection.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\Database\Connection;

class FakeConnection extends Connection {

  /**
   * @var string
   */
  protected $tablePrefix;

  /**
   * @var array
   */
  protected $connectionOptions;

  public function __construct($database_contents, $connection_options = array(), $prefix = '') {
    $this->databaseContents = $database_contents;
    $this->connectionOptions = $connection_options;
    $this->tablePrefix = $prefix;
  }

  public function select($table, $alias = NULL, array $options = array()) {
    return new FakeSelect($this->databaseContents, $table, $alias);
  }

  public function schema() {
    return new FakeDatabaseSchema($this->databaseContents);
  }

  public function insert($table, array $options = array()) {
    return new FakeInsert($this->databaseContents, $table);
  }

  public function update($table, array $options = array()) {
    return new FakeUpdate($this->databaseContents, $table);
  }

  public function merge($table, array $options = array()) {
    return new FakeMerge($this->databaseContents, $table);
  }

  public function truncate($table, array $options = array()) {
    return new FakeTruncate($this->databaseContents, $table);
  }

  public function tablePrefix($table = 'default') {
    return $this->tablePrefix;
  }

  public function getConnectionOptions() {
    return $this->connectionOptions;
  }

  public function query($query, array $args = array(), $options = array()) {
    throw new \Exception('Method not supported');
  }

  public function queryRange($query, $from, $count, array $args = array(), array $options = array()) {
    throw new \Exception('Method not supported');
  }

  public function queryTemporary($query, array $args = array(), array $options = array()) {
    throw new \Exception('Method not supported');
  }

  public function driver() {
    throw new \Exception('Method not supported');
  }

  public function databaseType() {
    throw new \Exception('Method not supported');
  }

  public function createDatabase($database) {
    // There is nothing to do.
  }

  public function mapConditionOperator($operator) {
    throw new \Exception('Method not supported');
  }

  public function nextId($existing_id = 0) {
    throw new \Exception('Method not supported');
  }
}
