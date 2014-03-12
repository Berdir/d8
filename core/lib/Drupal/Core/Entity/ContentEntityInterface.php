<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ContentEntityInterface.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Defines a common interface for all content entity objects.
 *
 * This interface builds upon the general interfaces provided by the typed data
 * API, while extending them with content entity-specific additions. I.e., a
 * content entity implements the ComplexDataInterface among others, thus is
 * complex data containing fields as its data properties. The contained fields
 * have to implement \Drupal\Core\Field\FieldItemListInterface,
 * which builds upon typed data interfaces as well.
 *
 * When implementing this interface which extends Traversable, make sure to list
 * IteratorAggregate or Iterator before this interface in the implements clause.
 *
 * @see \Drupal\Core\TypedData\TypedDataManager
 * @see \Drupal\Core\Field\FieldItemListInterface
 */
interface ContentEntityInterface extends EntityInterface, RevisionableInterface, TranslatableInterface {

  /**
   * Marks the translation identified by the given language code as existing.
   *
   * @param string $langcode
   *   The language code identifying the translation to be initialized.
   *
   * @todo Remove this as soon as translation metadata have been converted to
   *    regular fields.
   */
  public function initTranslation($langcode);

  /**
   * Provides base field definitions for an entity type.
   *
   * Implementations typically use the class \Drupal\Core\Field\FieldDefinition
   * for creating the field definitions; for example a 'name' field could be
   * defined as the following:
   * @code
   * $fields['name'] = FieldDefinition::create('string')
   *   ->setLabel(t('Name'));
   * @endcode
   *
   * If some elements in a field definition need to vary by bundle, use
   * \Drupal\Core\Entity\ContentEntityInterface::bundleFieldDefinitions().
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition. Useful when a single class is used for multiple,
   *   possibly dynamic entity types.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of base field definitions for the entity type, keyed by field
   *   name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   * @see \Drupal\Core\Entity\ContentEntityInterface::bundleFieldDefinitions()
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type);

  /**
   * Provides or alters field definitions for a specific bundle.
   *
   * The field definitions returned here for the bundle take precedence on the
   * base field definitions specified by baseFieldDefinitions() for the entity
   * type.
   *
   * @todo Provide a better DX for field overrides.
   *   See https://drupal.org/node/2145115.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition. Useful when a single class is used for multiple,
   *   possibly dynamic entity types.
   * @param string $bundle
   *   The bundle.
   * @param \Drupal\Core\Field\FieldDefinitionInterface[] $base_field_definitions
   *   The list of base field definitions.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of bundle field definitions, keyed by field name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   * @see \Drupal\Core\Entity\ContentEntityInterface::baseFieldDefinitions()
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions);

  /**
   * Returns whether the entity has a field with the given name.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return bool
   *   TRUE if the entity has a field with the given name. FALSE otherwise.
   */
  public function hasField($field_name);

  /**
   * Gets the definition of a contained field.
   *
   * @param string $name
   *   The name of the field.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface|false
   *   The definition of the field or FALSE if the field does not exist.
   */
  public function getFieldDefinition($name);

  /**
   * Gets an array of field definitions of all contained fields.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of field definitions, keyed by field name.
   *
   * @see \Drupal\Core\Entity\EntityManagerInterface::getFieldDefinitions()
   */
  public function getFieldDefinitions();

  /**
   * Gets a field item list.
   *
   * @param $field_name
   *   The name of the field to get; e.g., 'title' or 'name'.
   *
   * @throws \InvalidArgumentException
   *   If an invalid field name is given.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface
   *   The field item list, containing the field items.
   */
  public function get($field_name);

  /**
   * Sets a field value.
   *
   * @param $field_name
   *   The name of the property to set; e.g., 'title' or 'name'.
   * @param $value
   *   The value to set, or NULL to unset the field.
   * @param bool $notify
   *   (optional) Whether to notify the entity of the change. Defaults to
   *   TRUE. If the update stems from the entity, set it to FALSE to avoid
   *   being notified again.
   *
   * @throws \InvalidArgumentException
   *   If the specified field does not exist.
   *
   * @return $this
   */
  public function set($field_name, $value, $notify = TRUE);

  /**
   * Gets an array of field item lists.
   *
   * @param bool $include_computed
   *   If set to TRUE, computed fields are included. Defaults to FALSE.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface[]
   *   An array of field item lists implementing, keyed by field name.
   */
  public function getFields($include_computed = FALSE);

  /**
   * Gets an array of field values.
   *
   * Gets an array of plain field values including all non-computed
   * properties.
   *
   * @return array
   *   An array keyed by property name containing the property value.
   */
  public function getFieldValues();

  /**
   * Sets field values.
   *
   * @param array $values
   *   Array of values to set, keyed by field name.
   *
   * @return $this
   */
  public function setFieldValues(array $values);

  /**
   * React to changes to a child field.
   *
   * Note that this is invoked after any changes have been applied.
   *
   * @param $field_name
   *   The name of the field which is changed.
   */
  public function onChange($field_name);

}
