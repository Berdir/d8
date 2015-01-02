<?php

/**
 * @file
 * Contains \Drupal\config_test\Form\EntityTestEditForm.
 */

namespace Drupal\entity_test\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for adding "Test entity" entities.
 */
class EntityTestEditForm extends EntityTestFormBase {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $entity = $this->entity;

    drupal_set_message($this->t('%entity_type @id has been updated.', array(
      '@id' => $entity->id(),
      '%entity_type' => $entity->getEntityTypeId(),
    )));
  }

}
