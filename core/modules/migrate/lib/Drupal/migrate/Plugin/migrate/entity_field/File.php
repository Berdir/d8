<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\entity_field\File.
 */

namespace Drupal\migrate\Plugin\migrate\entity_field;

use Drupal\field\Entity\FieldInstance;
use Drupal\migrate\Plugin\MigrateEntityDestinationFieldInterface;

/**
 * @PluginID{"file")
 */
class File implements MigrateEntityDestinationFieldInterface {

  /**
   * {@inheritdoc}
   */
  public function import(FieldInstance $instance, array $values = NULL) {
    if ($values) {

    }
  }
}
