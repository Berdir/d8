<?php

/**
 * @file
 * Contains \Drupal\field\Plugin\field\widget\HiddenWidget.
 */

namespace Drupal\field\Plugin\field\widget;

use Drupal\Core\Entity\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'Hidden' widget.
 *
 * @FieldWidget(
 *   id = "hidden",
 *   label = @Translation("- Hidden -"),
 *   multiple_values = TRUE,
 *   weight = 50
 * )
 */
class HiddenWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    // The purpose of this widget is to be hidden, so nothing to do here.
    return array();
  }
}
