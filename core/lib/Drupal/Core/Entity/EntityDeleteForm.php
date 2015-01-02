<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityConfirmFormBase.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a generic base class for an entity deletion form.
 *
 * @ingroup entity_api
 */
class EntityDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the @entity-type %label', array(
      '@entity-type' => $this->entity->getEntityType()->getLowercaseLabel(),
      '%label' => $this->entity->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * Returns the message to display to the user after deleting the entity.
   *
   * @return string
   *   The translated string of the deletion message.
   */
  public function getDeletionMessage() {
    return $this->t('The @entity-type %label has been deleted.', array(
      '@entity-type' => $this->entity->getEntityType()->getLowercaseLabel(),
      '%label' => $this->entity->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url($this->entity->urlInfo());
  }

  /**
   * {@inheritdoc}
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#submit'] = array(array($this, 'delete'));
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message($this->getDeletionMessage());
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
