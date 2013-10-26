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
  protected $variables;

  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, CacheBackendInterface $cache, KeyValueStoreInterface $highwater_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $cache, $highwater_storage);
    $this->variables = $this->configuration['variables'];
  }

  protected function performRewind() {
    $this->result = array(array_map('unserialize', $this->query()->execute()->fetchAllKeyed()));
  }

  public function computeCount() {
    return 1;
  }

  /**
   * Unserialize each value.
   *
   * @return array
   */
  public function getNextRow() {
    return array_shift($this->result);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return drupal_map_assoc($this->variables);
  }

  /**
   * {@inheritdoc}
   */
  function query() {
    return $this->database
      ->select('variables', 'v')
      ->fields('v', array('name', 'value'))
      ->condition('name', $this->variables, 'IN');
  }
}
