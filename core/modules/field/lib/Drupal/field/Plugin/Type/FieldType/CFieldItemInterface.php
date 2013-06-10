<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\Type\FieldType\CFieldItemInterface.
 */

namespace Drupal\field\Plugin\Type\FieldType;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Field\FieldItemInterface;
use Drupal\field\Plugin\Core\Entity\Field;

/**
 * Interface definition for "Field type" plugins.
 */
interface CFieldItemInterface extends FieldItemInterface {

  /**
   * Returns the schema for the field.
   *
   * This method is static, because the field schema information is needed on
   * creation of the field. No field instances exist by then, and it is not
   * possible to instanciate a FieldItemInterface object yet.
   *
   * @param \Drupal\field\Plugin\Core\Entity\Field $field
   *   The field definition.
   *
   * @return array
   *   An associative array with the following key/value pairs:
   *   - columns: An array of Schema API column specifications, keyed by column
   *     name. This specifies what comprises a value for a given field. For
   *     example, a value for a number field is simply 'value', while a value
   *     for a formatted text field is the combination of 'value' and 'format'.
   *     It is recommended to avoid having the column definitions depend on
   *     field settings when possible. No assumptions should be made on how
   *     storage engines internally use the original column name to structure
   *     their storage.
   *   - indexes: (optional) An array of Schema API index definitions. Only
   *     columns that appear in the 'columns' array are allowed. Those indexes
   *     will be used as default indexes. Callers of field_create_field() can
   *     specify additional indexes or, at their own risk, modify the default
   *     indexes specified by the field-type module. Some storage engines might
   *     not support indexes.
   *   - foreign keys: (optional) An array of Schema API foreign key
   *     definitions. Note, however, that the field data is not necessarily
   *     stored in SQL. Also, the possible usage is limited, as you cannot
   *     specify another field as related, only existing SQL tables,
   *     such as {taxonomy_term_data}.
   */
  public static function schema(Field $field);

  /**
   * Returns a form for the field-level settings.
   *
   * Invoked from \Drupal\field_ui\Form\FieldEditForm to allow administrators to
   * configure field-level settings. If the field already has data, the form
   * should only include the settings that are safe to change.
   *
   * @todo: keep that remark below ? (comes from the phpdoc for the old hook_field_settings_form()).
   * @todo: Only the field type module knows which settings will affect the
   * field's schema, but only the field storage module knows what schema
   * changes are permitted once a field already has data. Probably we need an
   * easy way for a field type module to ask whether an update to a new schema
   * will be allowed without having to build up a fake $prior_field structure
   * for hook_field_update_forbid().
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param array $form_state
   *   The form state of the (entire) configuration form.
   * @param bool $has_data
   *   TRUE if the field already has data, FALSE if not.
   *   @todo ???
   *
   * @return
   *   The form definition for the field settings.
   */
  public function settingsForm(array $form, array &$form_state, $has_data);

  /**
   * Returns a form for the instance-level settings.
   *
   * Invoked from \Drupal\field_ui\Form\FieldInstanceEditForm to allow
   * administrators to configure instance-level settings.
   *
   * @param array $form
   *   The form where the settings form is being included in.
   * @param array $form_state
   *   The form state of the (entire) configuration form.
   *
   * @return array
   *   The form definition for the field instance settings.
   */
  public function instanceSettingsForm(array $form, array &$form_state);

  // @todo Decide what to do with those

  /**
   * Prepares field values prior to display.
   *
   * This method is invoked before the field values are handed to formatters
   * for display.
   *
   * This method operates on multiple entities. The $entities, $instances and
   * $items parameters are arrays keyed by entity ID. For performance reasons,
   * information for all entities should be loaded in a single query where
   * possible.
   *
   * Make changes or additions to field values by altering the $items parameter
   * by reference. There is no return value.
   *
   * @param array $entities
   *   The array of entities being displayed, keyed by entity ID.
   * @param $instances
   *   The array of \Drupal\field\Plugin\Core\Entity\FieldInstance objects for
   *   each entity, keyed by entity ID.
   * @param string $langcode
   *   The language associated to $items.
   * @param array $items
   *   Array of field values, keyed by entity ID.
   */
  public function prepareView(array $entities, array $instances, $langcode, array &$items);

  /**
   * Defines custom translation preparation behavior for field values.
   *
   * This mathod is called from field_attach_prepare_translation(), during the
   * process of preparing an entity for translation in a different language.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source_entity
   *   The source entity from which field values are being copied.
   * @param string $source_langcode
   *   The source language from which field values are being copied.
   */
  public function prepareTranslation(EntityInterface $source_entity, $source_langcode);

}
