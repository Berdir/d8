<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Form\VocabularyDeleteForm.
 */

namespace Drupal\taxonomy\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for taxonomy vocabulary.
 */
class VocabularyDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'taxonomy_vocabulary_confirm_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('taxonomy.vocabulary_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a vocabulary will delete all the terms in it. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->logger('taxonomy')->notice('Deleted vocabulary %name.', array('%name' => $this->entity->label()));
  }

}
