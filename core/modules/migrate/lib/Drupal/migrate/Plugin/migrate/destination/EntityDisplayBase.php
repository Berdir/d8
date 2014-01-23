<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDisplayBase.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;

abstract class EntityDisplayBase extends DestinationBase {

  const MODE_NAME = '';

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $values = array();
    // array_intersect_key() won't work because the order is important because
    // this is also the return value.
    foreach (array_keys($this->getIds()) as $id) {
      $values[$id] = $row->getDestinationProperty($id);
    }
    $entity = $this->getEntity($values['entity_type'], $values['bundle'], $values[static::MODE_NAME]);
    $entity
      ->setComponent($values['field_name'], $row->getDestinationProperty('options') ?: array())
      ->save();
    return array_values($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['entity_type']['type'] = 'string';
    $ids['bundle']['type'] = 'string';
    $ids[static::MODE_NAME]['type'] = 'string';
    $ids['field_name']['type'] = 'string';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    // TODO: Implement fields() method.
  }

  /**
   * @return \Drupal\Core\Entity\Display\EntityDisplayInterface
   */
  protected abstract function getEntity($entity_type, $bundle, $mode);

}
