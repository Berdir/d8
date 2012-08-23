<?php

/**
 * @file
 * Definition of Drupal\entity\EntityFormController.
 */

namespace Drupal\entity;

/**
 * Base class for entity form controllers.
 */
class EntityFormController implements EntityFormControllerInterface {

  /**
   * The name of the current operation.
   *
   * Subclasses may use this to implement different behaviors depending on its
   * value.
   *
   * @var string
   */
  protected $operation;

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::__construct().
   */
  public function __construct($operation) {
    $this->operation = $operation;
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::build().
   */
  public function build(array $form, array &$form_state, EntityInterface $entity) {

    // During the initial form build, add the entity to the form state for use
    // during form building and processing. During a rebuild, use what is in the
    // form state.
    if (!$this->getEntity($form_state)) {
      $this->init($form_state, $entity);
    }

    // Retrieve the form array using the possibly updated entity in form state.
    $entity = $this->getEntity($form_state);
    $form = $this->form($form, $form_state, $entity);

    // Retrieve and add the form actions array.
    $actions = $this->actionsElement($form, $form_state);
    if (!empty($actions)) {
      $form['actions'] = $actions;
    }

    return $form;
  }

  /**
   * Initialize the form state and the entity before the first form build.
   */
  protected function init(array &$form_state, EntityInterface $entity) {
    // Add the controller to the form state so it can be easily accessed by
    // module-provided form handlers there.
    $form_state['controller'] = $this;
    $this->setEntity($entity, $form_state);
    $this->prepareEntity($entity);
  }

  /**
   * Returns the actual form array to be built.
   *
   * @see Drupal\entity\EntityFormController::build()
   */
  public function form(array $form, array &$form_state, EntityInterface $entity) {
    // @todo Exploit the Property API to generate the default widgets for the
    // entity properties.
    $info = $entity->entityInfo();
    if (!empty($info['fieldable'])) {
      field_attach_form($entity->entityType(), $entity, $form, $form_state, $this->getFormLangcode($form_state));
    }
    return $form;
  }

  /**
   * Returns the action form element for the current entity form.
   */
  protected function actionsElement(array $form, array &$form_state) {
    $element = $this->actions($form, $form_state);

    // We cannot delete an entity that has not been created yet.
    if ($this->getEntity($form_state)->isNew()) {
      unset($element['delete']);
    }
    elseif (isset($element['delete'])) {
      // Move the delete action as last one, unless weights are explicitly
      // provided.
      $delete = $element['delete'];
      unset($element['delete']);
      $element['delete'] = $delete;
    }

    $count = 0;
    foreach (element_children($element) as $action) {
      $element[$action] += array(
        '#type' => 'submit',
        '#weight' => ++$count * 5,
      );
    }

    if (!empty($element)) {
      $element['#type'] = 'actions';
    }

    return $element;
  }

  /**
   * Returns an array of supported actions for the current entity form.
   */
  protected function actions(array $form, array &$form_state) {
    return array(
      // @todo Rename the action key from submit to save.
      'submit' => array(
        '#value' => t('Save'),
        '#validate' => array(
          array($this, 'validate'),
        ),
        '#submit' => array(
          array($this, 'submit'),
          array($this, 'save'),
        ),
      ),
      'delete' => array(
        '#value' => t('Delete'),
        // No need to validate the form when deleting the entity.
        '#submit' => array(
          array($this, 'delete'),
        ),
      ),
      // @todo Consider introducing a 'preview' action here, since it is used by
      // many entity types.
    );
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::validate().
   */
  public function validate(array $form, array &$form_state) {
    // @todo Exploit the Property API to validate the values submitted for the
    // entity properties.
    $entity = $this->buildEntity($form, $form_state);
    $info = $entity->entityInfo();

    if (!empty($info['fieldable'])) {
      field_attach_form_validate($entity->entityType(), $entity, $form, $form_state);
    }

    // @todo Remove this.
    // Execute legacy global validation handlers.
    unset($form_state['validate_handlers']);
    form_execute_handlers('validate', $form, $form_state);
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::submit().
   *
   * This is the default entity object builder function. It is called before any
   * other submit handler to build the new entity object to be passed to the
   * following submit handlers. At this point of the form workflow the entity is
   * validated and the form state can be updated, this way the subsequently
   * invoked handlers can retrieve a regular entity object to act on.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   A reference to a keyed array containing the current state of the form.
   */
  public function submit(array $form, array &$form_state) {
    $entity = $this->buildEntity($form, $form_state);
    $this->setEntity($entity, $form_state);
    return $entity;
  }

  /**
   * Form submission handler for the 'save' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   A reference to a keyed array containing the current state of the form.
   */
  public function save(array $form, array &$form_state) {
    // @todo Perform common save operations.
  }

  /**
   * Form submission handler for the 'delete' action.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   A reference to a keyed array containing the current state of the form.
   */
  public function delete(array $form, array &$form_state) {
    // @todo Perform common delete operations.
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::getFormLangcode().
   */
  public function getFormLangcode($form_state) {
    // @todo Introduce a new form language type (see hook_language_types_info())
    // to be used as the default active form language, should it be missing, so
    // that entity forms can be used to submit multilingual values.
    $language = $this->getEntity($form_state)->language();
    return !empty($language->langcode) ? $language->langcode : NULL;
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::buildEntity().
   */
  public function buildEntity(array $form, array &$form_state) {
    $entity = clone $this->getEntity($form_state);
    // @todo Move entity_form_submit_build_entity() here.
    // @todo Exploit the Property API to process the submitted entity property.
    entity_form_submit_build_entity($entity->entityType(), $entity, $form, $form_state);
    return $entity;
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::getEntity().
   */
  public function getEntity(array $form_state) {
    return isset($form_state['entity']) ? $form_state['entity'] : NULL;
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::setEntity().
   */
  public function setEntity(EntityInterface $entity, array &$form_state) {
    $form_state['entity'] = $entity;
  }

  /**
   * Prepares the entity object before the form is built first.
   */
  protected function prepareEntity(EntityInterface $entity) {
    // @todo Perform common prepare operations and add a hook.
  }

  /**
   * Implements Drupal\entity\EntityFormControllerInterface::getOperation().
   */
  public function getOperation() {
    return $this->operation;
  }
}
