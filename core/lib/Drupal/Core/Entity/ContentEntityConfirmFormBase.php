<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ContentEntityConfirmFormBase.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Form\ConfirmFormHelper;
use Drupal\Core\Form\ConfirmFormInterface;

/**
 * Provides a generic base class for an entity-based confirmation form.
 */
abstract class ContentEntityConfirmFormBase extends ContentEntityFormController implements ConfirmFormInterface {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormID() {
    return $this->entity->getEntityTypeId() . '_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Confirm');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelText() {
    return t('Cancel');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormName() {
    return 'confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#title'] = $this->getQuestion();

    $form['#attributes']['class'][] = 'confirmation';
    $form['description'] = array('#markup' => $this->getDescription());
    $form[$this->getFormName()] = array('#type' => 'hidden', '#value' => 1);

    // By default, render the form using theme_confirm_form().
    if (!isset($form['#theme'])) {
      $form['#theme'] = 'confirm_form';
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    // Do not attach fields to the confirm form.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    return array(
      'submit' => array(
        '#value' => $this->getConfirmText(),
        '#validate' => array(
          array($this, 'validate'),
        ),
        '#submit' => array(
          array($this, 'submitForm'),
        ),
      ),
      'cancel' => ConfirmFormHelper::buildCancelLink($this, $this->getRequest()),
    );
  }

  /**
   * {@inheritdoc}
   *
   * The save method makes no sense on EntityConfirmFormBase. Form submissions
   * should be processed by overriding the submitForm method.
   */
  public function save(array $form, array &$form_state) {}

  /**
   * {@inheritdoc}
   *
   * The delete method makes no sense on EntityConfirmFormBase. Form submissions
   * should be processed by overriding the submitForm method.
   */
  public function delete(array $form, array &$form_state) {}
}
