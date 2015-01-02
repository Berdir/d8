<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Form\VocabularyAddForm.
 */

namespace Drupal\taxonomy\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding roles.
 */
class VocabularyAddForm extends VocabularyFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $vocabulary = $this->entity;
    $edit_link = $this->getLinkGenerator()->generateFromUrl($this->t('Edit'), $this->entity->urlInfo());

    drupal_set_message($this->t('Created new vocabulary %name.', array('%name' => $vocabulary->name)));
    $this->logger('taxonomy')->notice('Created new vocabulary %name.', array('%name' => $vocabulary->name, 'link' => $edit_link));
    $form_state->setRedirectUrl($vocabulary->urlInfo('overview-form'));
  }

}
