<?php

/**
 * @file
 * Contains \Drupal\Core\Field\Plugin\Field\FieldWidget\StringWidget.
 */

namespace Drupal\user\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'user name' widget.
 *
 * @FieldWidget(
 *   id = "username",
 *   label = @Translation("User name field"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class UserNameWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $account = $element['#entity'];
    $user = \Drupal::currentUser();

    $is_anon = $account->isAnonymous();
    $is_admin = $user->hasPermission('administer users');;

    $element['value'] = $element + array(
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#attributes' => array(
        'class' => array('username'),
        'autocorrect' => 'off',
        'autocomplete' => 'off',
        'autocapitalize' => 'off',
        'spellcheck' => 'false',
      ),
      '#default_value' => (!$is_anon ? $account->getUsername() : ''),
      // Only show name field on registration form or user can change own
      // username.
      '#access' => ($is_anon || ($user->id() == $account->id() && $user->hasPermission('change own username')) || $is_admin),
    );
    $element['value']['#description'] = $this->t('Spaces are allowed; punctuation is not allowed except for periods, hyphens, apostrophes, and underscores.');

    return $element;
  }

}
