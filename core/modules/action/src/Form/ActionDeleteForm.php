<?php

/**
 * @file
 * Contains \Drupal\action\Form\ActionDeleteForm.
 */

namespace Drupal\action\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds a form to delete an action.
 */
class ActionDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('action.admin');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->logger('user')->notice('Deleted action %aid (%action)', array('%aid' => $this->entity->id(), '%action' => $this->entity->label()));
  }

}
