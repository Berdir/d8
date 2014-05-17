<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldWidget\PasswordWidget.
 */

namespace Drupal\Core\Field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'password' widget.
 *
 * @FieldWidget(
 *   id = "password",
 *   label = @Translation("Password field"),
 *   field_types = {
 *     "string"
 *   },
 *   settings = {
 *     "size" = ""
 *   }
 * )
 */
class PasswordWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element['value'] = $element + array(
      '#type' => 'password_confirm',
      '#size' => $this->getSetting('size'),
    );
    $element['value']['#description'] = $this->t('To change the current user password, enter the new password in both fields.');

    return $element;
  }
}
