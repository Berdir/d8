<?php

/**
 * @file
 * Contains \Drupal\node\Plugin\field\widget\TitleWidget.
 */

namespace Drupal\node\Plugin\field\widget;

use Drupal\field\Annotation\FieldWidget;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Field\FieldInterface;
use Drupal\field\Plugin\Type\Widget\WidgetBase;

/**
 * Plugin implementation of the 'node_title' widget.
 *
 * @FieldWidget(
 *   id = "node_title",
 *   module = "node",
 *   label = @Translation("Node title field"),
 *   field_types = {
 *     "text"
 *   }
 * )
 */
class TitleWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function form(EntityInterface $entity, $langcode, FieldInterface $items, array &$form, array &$form_state, $get_delta = NULL) {
    $field_name = $this->fieldDefinition->getFieldName();

    // @todo Make EntityManager::getFieldDefinitions() allow for per-bundle
    //   definitions of base fields, so that here, we could just call
    //   $this->fieldDefinition->getFieldLabel() instead.
    if ($entity->entityType() == 'node' && $field_name == 'title') {
      $node_type = node_type_load($entity->bundle());
      $label = $node_type->title_label;
    }
    else {
      $label = $this->fieldDefinition->getFieldLabel();
    }

    $addition[$field_name] = array(
      '#type' => 'textfield',
      '#title' => check_plain($label),
      '#required' => $this->fieldDefinition->isFieldRequired(),
      '#default_value' => isset($items[0]->value) ? $items[0]->value : '',
      '#maxlength' => $this->fieldDefinition->getFieldSetting('max_length'),
    );
    return $addition;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(EntityInterface $entity, $langcode, FieldInterface $items, array $form, array &$form_state) {
    $field_name = $this->fieldDefinition->getFieldName();

    // Extract the values from $form_state['values'].
    $path = array_merge($form['#parents'], array($field_name));
    $key_exists = NULL;
    $value = NestedArray::getValue($form_state['values'], $path, $key_exists);

    if ($key_exists) {
      $items->setValue(array(array('value' => $value)));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldInterface $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    return array();
  }
}
