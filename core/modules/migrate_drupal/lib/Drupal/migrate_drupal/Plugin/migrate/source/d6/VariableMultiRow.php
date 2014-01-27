<?php

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Row;

/**
 * Drupal 6 multiple variables source from database.
 *
 * @PluginID("drupal6_variable_multirow")
 */
class VariableMultiRow extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    return $this->select('variable', 'v')
      ->fields('v', array('name', 'value'))
      ->condition('name', $this->configuration['variables']);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return drupal_map_assoc($this->configuration['variables']);
  }

  public function prepareRow(Row $row) {
    if ($row->hasSourceProperty('value')) {
      $row->setSourceProperty('value', unserialize($row->getSourceProperty('value')));
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['fid']['type'] = 'integer';
    return $ids;
  }

}
