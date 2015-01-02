<?php

/**
 * @file
 * Contains \Drupal\filter\FilterFormatAddForm.
 */

namespace Drupal\filter;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding a filter format.
 */
class FilterFormatAddForm extends FilterFormatFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    drupal_set_message($this->t('Added text format %format.', array('%format' => $this->entity->label())));
    return $this->entity;
  }

}
