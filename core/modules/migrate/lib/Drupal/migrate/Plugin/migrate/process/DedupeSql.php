<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\process\DedupeSql.
 */


namespace Drupal\migrate\Plugin\migrate\process;
use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;


/**
 * @PluginId("dedupe_sql")
 */
class DedupeSql extends DedupeBase {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  protected function exists($value) {
    $field = $this->configuration['field'];
    return $this->getDatabase()
      ->select($this->configuration['table'], 't')
      ->fields('t', array($field))
      ->condition($field, $value)
      ->countQuery()
      ->execute()
      ->fetchField();
  }

  /**
   * @return \Drupal\Core\Database\Connection
   */
  protected function getDatabase() {
    if (!isset($this->database)) {
      $this->database = Drupal6SqlBase::getDatabaseConnection($this->migration);
    }
    return $this->database;
  }
}
