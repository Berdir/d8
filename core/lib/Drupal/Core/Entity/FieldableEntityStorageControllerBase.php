<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\FieldableEntityStorageControllerBase.
 */

namespace Drupal\Core\Entity;

use Drupal\Component\Utility\String;
use Drupal\Core\Field\PrepareCacheInterface;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldInstanceConfigInterface;
use Drupal\Core\Field\ConfigFieldItemListInterface;
use Drupal\field\FieldInterface;
use Drupal\field\FieldInstanceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FieldableEntityStorageControllerBase extends EntityStorageControllerBase implements FieldableEntityStorageControllerInterface {

  /**
   * The entity bundle key.
   *
   * @var string|bool
   */
  protected $bundleKey = FALSE;

  /**
   * Name of the entity class.
   *
   * @var string
   */
  protected $entityClass;

  /**
   * Constructs a FieldableEntityStorageControllerBase object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   */
  public function __construct(EntityTypeInterface $entity_type) {
    parent::__construct($entity_type);

    $this->bundleKey = $this->entityType->getKey('bundle');
    $this->entityClass = $this->entityType->getClass();
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type
    );
  }

  /**
   * {@inheritdoc}
   */
  public function create(array $values = array()) {
    $entity_class = $this->entityType->getClass();
    $entity_class::preCreate($this, $values);

    // We have to determine the bundle first.
    $bundle = FALSE;
    if ($this->bundleKey) {
      if (!isset($values[$this->bundleKey])) {
        throw new EntityStorageException(String::format('Missing bundle for entity type @type', array('@type' => $this->entityTypeId)));
      }
      $bundle = $values[$this->bundleKey];
    }
    $entity = new $entity_class(array(), $this->entityTypeId, $bundle);

    foreach ($entity as $name => $field) {
      if (isset($values[$name])) {
        $entity->$name = $values[$name];
      }
      elseif (!array_key_exists($name, $values)) {
        $entity->get($name)->applyDefaultValue();
      }
      unset($values[$name]);
    }

    // Set any passed values for non-defined fields also.
    foreach ($values as $name => $value) {
      $entity->$name = $value;
    }
    $entity->postCreate($this);

    // Modules might need to add or change the data initially held by the new
    // entity object, for instance to fill-in default values.
    $this->invokeHook('create', $entity);

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldCreate(FieldConfigInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldUpdate(FieldConfigInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onFieldDelete(FieldConfigInterface $field) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceCreate(FieldInstanceConfigInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceUpdate(FieldInstanceConfigInterface $instance) { }

  /**
   * {@inheritdoc}
   */
  public function onInstanceDelete(FieldInstanceConfigInterface $instance) { }

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
  public function onFieldItemsPurge(EntityInterface $entity, FieldInstanceConfigInterface $instance) {
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
   * @param \Drupal\field\FieldInstanceConfigInterface $instance
   *   The field instance.
   *
   * @return array
   *   The field values, in their canonical array format (numerically indexed
   *   array of items, each item being a property/value array).
   */
  abstract protected function readFieldItemsToPurge(EntityInterface $entity, FieldInstanceConfigInterface $instance);

  /**
   * Removes field data from storage during purge.
   *
   * @param EntityInterface $entity
   *   The entity whose values are being purged.
   * @param FieldInstanceConfigInterface $instance
   *   The field whose values are bing purged.
   */
  abstract protected function purgeFieldItems(EntityInterface $entity, FieldInstanceConfigInterface $instance);

  /**
   * {@inheritdoc}
   */
  public function onFieldPurge(FieldConfigInterface $field) { }

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
