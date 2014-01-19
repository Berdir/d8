<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityFormDisplay.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\migrate\Entity\Migration;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Row;

/**
 * @PluginId("entity_form_display")
 */
class EntityFormDisplay extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row) {
    $options = $row->getDestinationProperty('options') ?: array();
    $entity = $this->getEntity($row->getDestinationProperty('entity_type'), $row->getDestinationProperty('bundle'), $row->getDestinationProperty('form_mode'));
    $entity->setComponent($row->getDestinationProperty('field_name'), $options)->save();
    return array($entity->id());
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['entity_type']['type'] = 'string';
    $ids['bundle']['type'] = 'string';
    $ids['form_mode']['type'] = 'string';
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
   * @return \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   */
  protected function getEntity($entity_type, $bundle, $form_mode) {
    return entity_get_form_display($entity_type, $bundle, $form_mode);
  }

}
