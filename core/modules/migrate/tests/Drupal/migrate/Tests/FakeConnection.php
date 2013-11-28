<?php

/**
 * @file
 * Contains \Drupal\migrate\Tests\FakeConnection.
 */

namespace Drupal\migrate\Tests;

class FakeConnection {

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

  public function select($base_table, $base_alias = NULL) {
    return new FakeSelect($this->databaseContents, $base_table, $base_alias);
  }

  public function schema() {
    return new FakeDatabaseSchema($this->databaseContents);
  }

  public function insert($table) {
    return new FakeInsert($this->databaseContents, $table);
  }

  public function update($table) {
    return new FakeUpdate($this->databaseContents, $table);
  }

  public function merge($table) {
    return new FakeMerge($this->databaseContents, $table);
  }

  public function truncate($table) {
    return new FakeTruncate($this->databaseContents, $table);
  }

  public function tablePrefix() {
    return $this->tablePrefix;
  }

  public function getConnectionOptions() {
    return $this->connectionOptions;
  }

}
