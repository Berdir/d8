<?php

/**
 * @file
 * Contains Drupal\Core\Config\Entity\ConfigEntityBundleBase.
 */

namespace Drupal\Core\Config\Entity;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * A base class for config entity types that act as bundles.
 *
 * Entity types that want to use this base class must use bundle_of in their
 * annotation to specify for which entity type they are providing bundles for.
 */
abstract class ConfigEntityBundleBase extends ConfigEntityBase {

  /**
   * Renames displays when a bundle is renamed.
   */
  protected function renameDisplays() {
    // Rename entity displays.
    $entity_type = \Drupal::entityManager()->getDefinition('entity_view_display');
    if ($this->getOriginalId() !== $this->id()) {
      $ids = \Drupal::configFactory()->listAll('core.entity_view_display.' . $this->getEntityType()->getBundleOf() . '.' . $this->getOriginalId() . '.');
      foreach ($ids as $id) {
        $id = ConfigEntityStorage::getIDFromConfigName($id, $entity_type->getConfigPrefix());
        $display = EntityViewDisplay::load($id);
        $new_id = $this->getEntityType()->getBundleOf() . '.' . $this->id() . '.' . $display->mode;
        $display->set('id', $new_id);
        $display->bundle = $this->id();
        $display->save();
      }
    }

    // Rename entity form displays.
    $entity_type = \Drupal::entityManager()->getDefinition('entity_form_display');
    if ($this->getOriginalId() !== $this->id()) {
      $ids = \Drupal::configFactory()->listAll('core.entity_form_display.' . $this->getEntityType()->getBundleOf() . '.' . $this->getOriginalId() . '.');
      foreach ($ids as $id) {
        $id = ConfigEntityStorage::getIDFromConfigName($id, $entity_type->getConfigPrefix());
        $form_display = EntityFormDisplay::load($id);
        $new_id = $this->getEntityType()->getBundleOf() . '.' . $this->id() . '.' . $form_display->mode;
        $form_display->set('id', $new_id);
        $form_display->bundle = $this->id();
        $form_display->save();
      }
    }
  }

  /**
   * Deletes display if a bundle is deleted.
   */
  function deleteDisplays() {
    // Remove entity displays of the deleted bundle.
    $entity_type = \Drupal::entityManager()->getDefinition('entity_view_display');
    $ids = \Drupal::configFactory()->listAll('core.entity_view_display.' . $this->getEntityType()->getBundleOf() . '.' . $this->id() . '.');
    foreach ($ids as &$id) {
      $id = ConfigEntityStorage::getIDFromConfigName($id, $entity_type->getConfigPrefix());
    }
    entity_delete_multiple('entity_view_display', $ids);

    // Remove entity form displays of the deleted bundle.
    $entity_type = \Drupal::entityManager()->getDefinition('entity_form_display');
    $ids = \Drupal::configFactory()->listAll('core.entity_form_display.' . $this->getEntityType()->getBundleOf() . '.' . $this->id() . '.');
    foreach ($ids as &$id) {
      $id = ConfigEntityStorage::getIDFromConfigName($id, $entity_type->getConfigPrefix());
    }
    entity_delete_multiple('entity_form_display', $ids);
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update) {
      entity_invoke_bundle_hook('create', $this->getEntityType()->getBundleOf(), $this->id());
    }
    elseif ($this->getOriginalId() != $this->id()) {
      $this->renameDisplays();
      entity_invoke_bundle_hook('rename', $this->getEntityType()->getBundleOf(), $this->getOriginalId(), $this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);

    foreach ($entities as $entity) {
      $entity->deleteDisplays();
      entity_invoke_bundle_hook('delete', $entity->getEntityType()->getBundleOf(), $entity->id());
    }
  }

}
