<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Form\TermEditForm.
 */

namespace Drupal\taxonomy\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form editing terms.
 */
class TermEditForm extends TermFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var \Drupal\taxonomy\TermInterface $term */
    $term = $this->entity;
    $edit_link = $this->getLinkGenerator()->generateFromUrl($this->t('Edit'), $term->urlInfo('edit-form'));

    drupal_set_message($this->t('Updated term %term.', array('%term' => $term->getName())));
    $this->logger('taxonomy')->notice('Updated term %term.', array(
      '%term' => $term->getName(),
      'link' => $edit_link,
    ));
  }

}
