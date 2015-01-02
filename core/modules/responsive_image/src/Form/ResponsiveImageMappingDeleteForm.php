<?php

/**
 * @file
 * Contains \Drupal\responsive_image\Form\ResponsiveImageMappingActionConfirm.
 */

namespace Drupal\responsive_image\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class ResponsiveImageMappingDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('responsive_image.mapping_page');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->logger('responsive_image')->notice('Responsive image mapping %label has been deleted.', array('%label' => $this->entity->label()));
  }

}
