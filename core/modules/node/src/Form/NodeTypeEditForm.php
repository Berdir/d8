<?php

/**
 * @file
 * Contains \Drupal\node\Form\NodeTypeEditForm.
 */

namespace Drupal\node\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding node types.
 */
class NodeTypeEditForm extends NodeTypeFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $type = $this->entity;
    $t_args = array('%name' => $type->label());

    drupal_set_message(t('The content type %name has been updated.', $t_args));
  }

}
