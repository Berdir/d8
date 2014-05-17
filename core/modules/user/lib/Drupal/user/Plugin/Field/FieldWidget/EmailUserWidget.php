<?php

/**
 * @file
 * Contains \Drupal\user\Plugin\Field\FieldWidget\EmailUserWidget.
 */

namespace Drupal\user\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;

/**
 * Plugin implementation of the 'email_user' widget.
 *
 * @FieldWidget(
 *   id = "email_user",
 *   label = @Translation("User e-mail"),
 *   field_types = {
 *     "email"
 *   }
 * )
 */
class EmailUserWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $account = $element['#entity'];
    $user = \Drupal::currentUser();

    $element['value'] = $element + array(
      '#type' => 'email',
      '#description' => $this->t('A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.'),
      // The mail field is NOT required if account originally had no mail set
      // and the user performing the edit has 'administer users' permission.
      // This allows users without e-mail address to be edited and deleted.
      '#required' => !(!$account->getEmail() && $user->hasPermission('administer users')),
      '#default_value' => (!$account->isAnonymous() ? $account->getEmail() : ''),
      '#attributes' => array('autocomplete' => 'off'),
    );

    return $element;
  }

}
