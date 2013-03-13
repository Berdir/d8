<?php

/**
 * @file
 * Definition of Drupal\taxonomy\VocabularyStorageController.
 */

namespace Drupal\taxonomy;

use Drupal\Core\Config\Entity\ConfigStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a controller class for taxonomy vocabularies.
 */
class VocabularyStorageController extends ConfigStorageController {

  /**
   * Overrides Drupal\Core\Config\Entity\ConfigStorageController::postSave().
   */
  protected function postSave(EntityInterface $entity, $update) {
    if (!$update) {
      field_attach_create_bundle('taxonomy_term', $entity->id());
    }
    elseif ($entity->getOriginalID() != $entity->id()) {
      field_attach_rename_bundle('taxonomy_term', $entity->getOriginalID(), $entity->id());
    }
    parent::postSave($entity, $update);
    $this->resetCache($update ? array($entity->getOriginalID()) : array());
  }

  /**
   * Overrides Drupal\Core\Config\Entity\ConfigStorageController::preDelete().
   */
  protected function preDelete($entities) {
    parent::preDelete($entities);
    // Only load terms without a parent, child terms will get deleted too.
    $tids = db_query('SELECT t.tid FROM {taxonomy_term_data} t INNER JOIN {taxonomy_term_hierarchy} th ON th.tid = t.tid WHERE t.vid IN (:vids) AND th.parent = 0', array(':vids' => array_keys($entities)))->fetchCol();
    taxonomy_term_delete_multiple($tids);
  }

  /**
   * Overrides Drupal\Core\Config\Entity\ConfigStorageController::postDelete().
   */
  protected function postDelete($entities) {
    foreach ($entities as $entity) {
      field_attach_delete_bundle('taxonomy_term', $entity->id());
    }

    parent::postDelete($entities);
    $this->resetCache(array_keys($entities));
  }

  /**
   * Overrides Drupal\Core\Config\Entity\ConfigStorageController::resetCache().
   */
  public function resetCache(array $ids = NULL) {
    drupal_static_reset('taxonomy_vocabulary_get_names');
    parent::resetCache($ids);
    cache_invalidate_tags(array('content' => TRUE));
    entity_info_cache_clear();
  }

}
