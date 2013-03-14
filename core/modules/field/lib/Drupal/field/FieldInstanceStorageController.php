<?php

/**
 * @file
 * Contains Drupal\field\FieldInstanceStorageController.
 */

namespace Drupal\field;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\Entity\ConfigStorageController;

/**
 * Controller class for field instances.
 */
class FieldInstanceStorageController extends ConfigStorageController {

  /**
   * Overrides \Drupal\Core\Config\Entity\ConfigStorageController::importDelete().
   */
  public function importDelete($name, Config $new_config, Config $old_config) {
    $config = $old_config->get();
    // In case the field has been deleted, the instance will be deleted by then
    // already.
    if (!empty($config)) {
      parent::importDelete($name, $new_config, $old_config);
    }
  }

}
