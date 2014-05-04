<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Plugin\Type\Selection\SelectionBroken.
 */

namespace Drupal\entity_reference\Plugin\Type\Selection;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * A null implementation of SelectionInterface.
 */
class SelectionBroken implements SelectionInterface {

  /**
   * {@inheritdoc}
   */
  public static function settingsForm(FieldDefinitionInterface $field_definition) {
    $form['selection_handler'] = array(
      '#markup' => t('The selected selection handler is broken.'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0, $langcode = NULL) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function countReferenceableEntities($match = NULL, $match_operator = 'CONTAINS') {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function validateAutocompleteInput($input, &$element, &$form_state, $form, $strict = TRUE) { }

  /**
   * {@inheritdoc}
   */
  public function entityQueryAlter(SelectInterface $query) { }

}
