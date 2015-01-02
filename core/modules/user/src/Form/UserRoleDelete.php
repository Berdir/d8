<?php

/**
 * @file
 * Contains \Drupal\user\Form\UserRoleDelete.
 */

namespace Drupal\user\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for Role entity.
 */
class UserRoleDelete extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('user.role_list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->logger('user')->notice('Role %name has been deleted.', array('%name' => $this->entity->label()));
  }

}
