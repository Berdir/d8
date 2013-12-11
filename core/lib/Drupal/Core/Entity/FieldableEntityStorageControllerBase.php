<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\FieldableEntityStorageControllerBase.
 */

namespace Drupal\Core\Entity;

use Drupal\field\FieldInterface;
use Drupal\field\FieldInstanceInterface;

abstract class FieldableEntityStorageControllerBase extends EntityStorageControllerBase implements FieldableEntityStorageControllerInterface {

  /**
   * {@inheritdoc}
   */
  public function onFieldCreate(FieldInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate(FieldInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldDelete(FieldInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceCreate(FieldInstanceInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceUpdate(FieldInstanceInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceDelete(FieldInstanceInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function onBundleCreate($bundle) { }

  /**
   * {@inheritdoc}
   */
  public function onBundleRename($bundle, $bundle_new) { }

  /**
   * {@inheritdoc}
   */
  public function onBundleDelete($bundle) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldItemsPurge(EntityInterface $entity, FieldInstanceInterface $instance) {
    if ($values = $this->readFieldItemsToPurge($entity, $instance)) {
      $items = \Drupal::typedDataManager()->create($instance, $values, $instance->getName(), $entity);
      $items->delete();
    }
    $this->purgeFieldItems($entity, $instance);
  }

  /**
   * Reads values to be purged for a single field of a single entity.
   *
   * This method is called during field data purge, on fields for which
   * onFieldDelete() or onFieldInstanceDelete() has previously run.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\field\FieldInstanceInterface $instance
   *   The field instance.
   *
   * @return array
   *   The field values, in their canonical array format (numerically indexed
   *   array of items, each item being a property/value array).
   */
  abstract protected function readFieldItemsToPurge(EntityInterface $entity, FieldInstanceInterface $instance);

  /**
   * Removes field data from storage during purge.
   *
   * @param EntityInterface $entity
   *   The entity whose values are being purged.
   * @param FieldInstanceInterface $instance
   *   The field whose values are bing purged.
   */
  abstract protected function purgeFieldItems(EntityInterface $entity, FieldInstanceInterface $instance);

  /**
   * {@inheritdoc}
   */
  public function onFieldPurge(FieldInterface $field) { }

  /**
   * Checks translation statuses and invoke the related hooks if needed.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity being saved.
   */
  protected function invokeTranslationHooks(ContentEntityInterface $entity) {
    $translations = $entity->getTranslationLanguages(FALSE);
    $original_translations = $entity->original->getTranslationLanguages(FALSE);
    $all_translations = array_keys($translations + $original_translations);

    // Notify modules of translation insertion/deletion.
    foreach ($all_translations as $langcode) {
      if (isset($translations[$langcode]) && !isset($original_translations[$langcode])) {
        $this->invokeHook('translation_insert', $entity->getTranslation($langcode));
      }
      elseif (!isset($translations[$langcode]) && isset($original_translations[$langcode])) {
        $this->invokeHook('translation_delete', $entity->getTranslation($langcode));
      }
    }
  }

  /**
   * Invokes a method on the Field objects within an entity.
   *
   * @param string $method
   *   The method name.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   */
  protected function invokeFieldMethod($method, ContentEntityInterface $entity) {
    foreach (array_keys($entity->getTranslationLanguages()) as $langcode) {
      $translation = $entity->getTranslation($langcode);
      foreach ($translation->getProperties(TRUE) as $field) {
        $field->$method();
      }
    }
  }

}
