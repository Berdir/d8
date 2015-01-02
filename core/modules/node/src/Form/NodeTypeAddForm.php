<?php

/**
 * @file
 * Contains \Drupal\node\Form\NodeTypeAddForm.
 */

namespace Drupal\node\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding node types.
 */
class NodeTypeAddForm extends NodeTypeFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $type = $this->entity;
    $t_args = array('%name' => $type->label());

    drupal_set_message(t('The content type %name has been added.', $t_args));
    $context = array_merge($t_args, array('link' => l(t('View'), 'admin/structure/types')));
    $this->logger('node')->notice('Added content type %name.', $context);
  }

}
