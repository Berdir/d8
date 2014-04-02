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
 *   id = "author_autocomplete",
 *   label = @Translation("Author user reference autocomplete"),
 *   field_types = {
 *     "entity_reference",
 *   },
 *   settings = {
 *     "route_name" = "",
 *   }
 * )
 */
class AuthorAutocompleteWidget extends RouteBasedAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $entity = $element['#entity'];
    $element['target_id']['#default_value'] = $entity->getOwner()? $entity->getOwner()->getUsername() : '';

    $user_config = \Drupal::config('user.settings');
    $element['target_id']['#description'] = $this->t('Leave blank for %anonymous.', array('%anonymous' => $user_config->get('anonymous')));

    $element['target_id']['#element_validate'] = array(array($this, 'elementValidate'));

    return $element;
  }

  /**
   * Validates an element.
   *
   * @todo Convert to massageFormValues() after https://drupal.org/node/2226723 lands.
   */
  public function elementValidate($element, &$form_state, $form) {
    $form_builder = \Drupal::formBuilder();
    $value = $element['#value'];
    // The use of empty() is mandatory in the context of usernames
    // as the empty string denotes the anonymous user. In case we
    // are dealing with an anonymous user we set the user ID to 0.
    if (empty($value)) {
      $value = 0;
    }
    else {
      $account = user_load_by_name($value);
      if ($account !== FALSE) {
        $value = $account->id();
      }
      else {
        // Edge case: a non-existing numeric username should not be treated as
        // a user ID (entity reference target_id). The ValidReference constraint
        // would consider this a valid user ID, therefore we need additional
        // validation here.
        $form_builder->setError($element, $form_state, $this->t('The username %name does not exist.', array('%name' => $value)));
        $value = NULL;
      }
    }
    $form_builder->setValue($element, $value, $form_state);
  }

}
