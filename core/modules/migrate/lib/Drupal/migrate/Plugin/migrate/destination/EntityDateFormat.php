<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\destination\EntityDateFormat.
 */

namespace Drupal\migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityInterface;
use Drupal\migrate\Row;

/**
 * @PluginId("entity_date_format")
 */
class EntityDateFormat extends Entity {

  /**
   * {@inheritdoc}
   */
  protected function update(EntityInterface $entity, Row $row) {
    /** @var \Drupal\system\Entity\DateFormat $entity */
    foreach ($row->getRawDestination() as $property => $value) {
      $keys = explode(':', $property);
      if ($keys[0] == 'pattern') {
        $entity->setPattern($value, $keys[1]);
      }
      else {
        $this->setValue($entity, $keys, $value);
      }
    }
  }
}
