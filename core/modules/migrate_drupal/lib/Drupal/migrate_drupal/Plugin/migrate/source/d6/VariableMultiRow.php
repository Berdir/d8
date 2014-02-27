<?php

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 6 multiple variables source from database.
 *
 * Unlike the d6_variable source plugin, this one returns one row per
 * variable.
 *
 * @MigrateSource(
 *   id = "d6_variable_multirow"
 * )
 */
class VariableMultiRow extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('variable', 'v')
      ->fields('v', array('name', 'value'))
      ->condition('name', $this->configuration['variables']);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'name' => $this->t('Name'),
      'value' => $this->t('Value'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if ($value = $row->getSourceProperty('value')) {
      $row->setSourceProperty('value', unserialize($value));
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
