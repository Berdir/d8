<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\MigrateStorageController.
 */

namespace Drupal\migrate;

use Drupal\Core\Config\Entity\ConfigStorageController;

/**
 * Storage controller for migration entities.
 */
class MigrationStorageController extends ConfigStorageController {

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
      $ids = array_flip($ids);
    }
    else {
      $ids_to_load = NULL;
    }
    /** @var \Drupal\migrate\Entity\MigrationInterface[] $entities */
    $entities = parent::loadMultiple($ids_to_load);
    if (!isset($ids)) {
      // Changing the array being foreach()'d is not a good idea.
      $return = array();
      foreach ($entities as $entity_id => $entity) {
        if ($plugin = $entity->getLoadPlugin()) {
          $return += $plugin->loadMultiple();
        }
        else {
          $return[$entity_id] = $entity;
        }
      }
      $entities = $return;
    }
    else {
      foreach ($dynamic_ids as $base_id => $sub_ids) {
        $entity = $entities[$base_id];
        if ($plugin = $entity->getLoadPlugin()) {
          unset($entities[$base_id]);
          $entities += $plugin->loadMultiple($sub_ids);
        }
      }
    }
    return $entities;
  }

}
