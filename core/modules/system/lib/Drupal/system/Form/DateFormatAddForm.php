<?php

/**
 * @file
 * Contains \Drupal\system\Form\DateFormatAddForm.
 */

namespace Drupal\system\Form;

/**
 * Provides a form controller for adding a date format.
 */
class DateFormatAddForm extends DateFormatFormBase {

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Add format');
    return $actions;
  }

}
