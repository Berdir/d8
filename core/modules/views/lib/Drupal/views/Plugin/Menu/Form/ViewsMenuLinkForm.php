<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\Menu\Form\ViewsMenuLinkForm.
 */

namespace Drupal\views\Plugin\Menu\Form;

use Drupal\Core\Menu\Form\MenuLinkDefaultForm;

class ViewsMenuLinkForm extends MenuLinkDefaultForm {

  public function buildEditForm(array &$form, array &$form_state) {
    $form = parent::buildEditForm($form, $form_state);

    $form['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $this->menuLink->getTitle(),
    );

    return $form;
  }

  public function extractFormValues(array &$form, array &$form_state) {
    $definition = parent::extractFormValues($form, $form_state);
    $definition['title'] = $form_state['values']['title'];

    return $definition;
  }

}

