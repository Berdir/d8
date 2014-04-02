<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldWidget\RouteBasedAutocompleteWidget.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'route based autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "route_based_autocomplete",
 *   label = @Translation("Entity reference autocomplete (route-based)"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   settings = {
 *     "route_name" = "",
 *   }
 * )
 */
class RouteBasedAutocompleteWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element['target_id'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->value,
      '#autocomplete_route_name' => $this->getSetting('route_name'),
    );

    return $element;
  }

}
