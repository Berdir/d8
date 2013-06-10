<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Type\FieldType\PrepareCacheInterface.
 */

namespace Drupal\field\Plugin\Type\FieldType;

use Drupal\field\Plugin\Type\FieldType\CFieldItemInterface;

/**
 * Interface definition for "Field type" plugins.
 */
interface PrepareCacheInterface extends CFieldItemInterface {

  /**
   * Massages loaded field values before they enter the field cache.
   *
   * You should never load fieldable entities within this method, since this is
   * likely to cause infinite recursions. Use the prepareView() method instead.
   */
  public function prepareCache();

}
