<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Plugin\Type\Selection\SelectionInterface.
 */

namespace Drupal\entity_reference\Plugin\Type\Selection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Interface definition for Entity Reference Selection plugins.
 *
 * @see \Drupal\entity_reference\Plugin\entity_reference\selection\SelectionBase
 * @see \Drupal\entity_reference\Plugin\Type\SelectionPluginManager
 * @see \Drupal\entity_reference\Annotation\EntityReferenceSelection
 * @see \Drupal\entity_reference\Plugin\Derivative\SelectionBase
 * @see plugin_api
 */
interface SelectionInterface {

  /**
   * Returns a list of referenceable entities.
   *
   * @return array
   *   A nested array of entities, the first level is keyed by the
   *   entity bundle, which contains an array of entity labels (safe HTML),
   *   keyed by the entity ID.
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0);

  /**
   * Counts entities that are referenceable by a given field.
   *
   * @return int
   *   The number of referenceable entities.
   */
  public function countReferenceableEntities($match = NULL, $match_operator = 'CONTAINS');

  /**
   * Validates that entities can be referenced by this field.
   *
   * @return array
   *   An array of valid entity IDs.
   */
  public function validateReferenceableEntities(array $ids);

  /**
   * Validates input from an autocomplete widget that has no ID.
   *
   * @param string $input
   *   Single string from autocomplete widget.
   * @param array $element
   *   The form element to set a form error.
   * @param boolean $strict
   *   Whether to trigger a form error if an element from $input (eg. an entity)
   *   is not found. Defaults to TRUE.
   *
   * @return integer|null
   *   Value of a matching entity ID, or NULL if none.
   *
   * @see \Drupal\entity_reference\Plugin\Field\FieldWidget::elementValidate()
   */
  public function validateAutocompleteInput($input, &$element, &$form_state, $form, $strict = TRUE);

  /**
   * Allows the selection to alter the SelectQuery generated by EntityFieldQuery.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   A Select Query object.
   */
  public function entityQueryAlter(SelectInterface $query);

  /**
   * Generates the settings form for this selection.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the selection is associated.
   *
   * @return array
   *   A Form API array.
   */
  public static function settingsForm(FieldDefinitionInterface $field_definition);

}
