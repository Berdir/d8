<?php

/**
 * @file
 * Contains \Drupal\config_test\Form\EntityTestAddForm.
 */

namespace Drupal\entity_test\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding "Test entity" entities.
 */
class EntityTestAddForm extends EntityTestFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $entity = $this->entity;

    drupal_set_message($this->t('%entity_type @id has been created.', array(
      '@id' => $entity->id(),
      '%entity_type' => $entity->getEntityTypeId(),
    )));
  }

}
