<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Variable.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 variable source from database.
 *
 * @PluginId("drupal6_variable")
 */
class Variable extends SqlBase {

  /**
   * The variable names to fetch.
   *
   * @var array
   */
  protected $sourceNames;

  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, CacheBackendInterface $cache, KeyValueStoreInterface $highwater_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $cache, $highwater_storage);
    $this->sourceNames = $this->configuration['sourceNames'];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('variables', 'v')
      ->fields('v', array('name', 'value'))
      ->condition('name', explode(',', $this->sourceNames), 'IN');
    return $query;
  }

  /**
   * Unserialize each value.
   *
   * @return array
   */
  public function getNextRow() {
    $row = parent::getNextRow();
    foreach ($row as $name => $value) {
      $row[$name] = unserialize($value);
    }
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return drupal_map_assoc($this->sourceNames);
  }

}
