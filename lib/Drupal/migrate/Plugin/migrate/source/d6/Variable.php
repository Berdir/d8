<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Variable.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

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

  /**
   * {@inheritdoc}
   */
  function query() {

    // @todo: determine how to pass in arguments via plugin config constructor
    $this->sourceNames = $this->configuration['sourceNames'];

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
