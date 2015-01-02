<?php

/**
 * @file
 * Contains \Drupal\taxonomy\Form\VocabularyFormBase.
 */

namespace Drupal\taxonomy\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\language\Entity\ContentLanguageSettings;

/**
 * Base form for vocabulary edit forms.
 */
abstract class VocabularyFormBase extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $vocabulary = $this->entity;
    if ($vocabulary->isNew()) {
      $form['#title'] = $this->t('Add vocabulary');
    }
    else {
      $form['#title'] = $this->t('Edit vocabulary');
    }

    $form['name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $vocabulary->name,
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['vid'] = array(
      '#type' => 'machine_name',
      '#default_value' => $vocabulary->id(),
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#machine_name' => array(
        'exists' => 'taxonomy_vocabulary_load',
        'source' => array('name'),
      ),
    );
    $form['description'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Description'),
      '#default_value' => $vocabulary->description,
    );

    // $form['langcode'] is not wrapped in an
    // if ($this->moduleHandler->moduleExists('language')) check because the
    // language_select form element works also without the language module being
    // installed. http://drupal.org/node/1749954 documents the new element.
    $form['langcode'] = array(
      '#type' => 'language_select',
      '#title' => $this->t('Vocabulary language'),
      '#languages' => LanguageInterface::STATE_ALL,
      '#default_value' => $vocabulary->language()->getId(),
    );
    if ($this->moduleHandler->moduleExists('language')) {
      $form['default_terms_language'] = array(
        '#type' => 'details',
        '#title' => $this->t('Terms language'),
        '#open' => TRUE,
      );
      $form['default_terms_language']['default_language'] = array(
        '#type' => 'language_configuration',
        '#entity_information' => array(
          'entity_type' => 'taxonomy_term',
          'bundle' => $vocabulary->id(),
        ),
        '#default_value' => ContentLanguageSettings::loadByEntityTypeBundle('taxonomy_term', $vocabulary->id()),
      );
    }
    // Set the hierarchy to "multiple parents" by default. This simplifies the
    // vocabulary form and standardizes the term form.
    $form['hierarchy'] = array(
      '#type' => 'value',
      '#value' => '0',
    );

    return parent::form($form, $form_state, $vocabulary);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // If we are displaying the delete confirmation skip the regular actions.
    if (!$form_state->get('confirm_delete')) {
      $actions = parent::actions($form, $form_state);
      // We cannot leverage the regular submit handler definition because we
      // have button-specific ones here. Hence we need to explicitly set it for
      // the submit action, otherwise it would be ignored.
      if ($this->moduleHandler->moduleExists('content_translation')) {
        array_unshift($actions['submit']['#submit'], 'content_translation_language_configuration_element_submit');
      }
      return $actions;
    }
    else {
      return array();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $vocabulary = $this->entity;

    // Prevent leading and trailing spaces in vocabulary names.
    $vocabulary->name = trim($vocabulary->name);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $vocabulary = $this->entity;

    $form_state->setValue('vid', $vocabulary->id());
    $form_state->set('vid', $vocabulary->id());
  }

}
