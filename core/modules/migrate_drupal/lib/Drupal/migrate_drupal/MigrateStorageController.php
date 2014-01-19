<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\MigrateStorageController.
 */

namespace Drupal\migrate_drupal;


use Drupal\Core\Config\Entity\ConfigStorageController;

class MigrateStorageController extends ConfigStorageController {

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL) {
    $ids_to_load = array();
    $dynamic_ids = array();
    if (isset($ids)) {
      foreach ($ids as $id) {
        if (($n = strpos($id, ':')) !== FALSE) {
          $base_id = substr($id, 0, $n);
          $ids_to_load[] = $base_id;
          $sub_id = substr($id, $n + 1);
          if ($sub_id == '*') {
            $dynamic_ids[$base_id] = NULL;
          }
          elseif (!isset($dynamic_ids[$base_id]) || is_array($dynamic_ids[$base_id])) {
            $dynamic_ids[$base_id][] = $sub_id;
          }
        }
        else {
          $ids_to_load[] = $id;
        }
      }
    }
    else {
      $ids_to_load = NULL;
    }
    $entities = parent::loadMultiple($ids_to_load);
    if (!isset($ids)) {
      foreach ($entities as $entity) {
        if ($plugin = $entity->getLoadPlugin()) {
          $entities += $plugin->loadMultiple();
        }
      }
    }
    else {
      foreach ($dynamic_ids as $base_id => $sub_ids) {
        $entity = $entities[$base_id];
        if ($plugin = $entity->getLoadPlugin()) {
          $entities += $plugin->loadMultiple($sub_ids);
        }
      }
    }
    return $entities;
  }

}
