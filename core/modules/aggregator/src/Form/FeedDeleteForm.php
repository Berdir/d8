<?php

/**
 * @file
 * Contains \Drupal\aggregator\Form\FeedDeleteForm.
 */

namespace Drupal\aggregator\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a feed.
 */
class FeedDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('aggregator.admin_overview');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->logger('aggregator')->notice('Feed %feed deleted.', array('%feed' => $this->entity->label()));
  }

}
