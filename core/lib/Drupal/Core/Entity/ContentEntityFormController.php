<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\ContentEntityFormController.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\entity\Entity\EntityFormDisplay;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Entity form controller variant for content entity types.
 *
 * @see \Drupal\Core\ContentEntityBase
 */
class ContentEntityFormController extends EntityFormController implements ContentEntityFormControllerInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructs a ContentEntityFormController object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    $this->getFormDisplay($form_state)->buildForm($this->entity, $form, $form_state);

    // Add a process callback so we can assign weights and hide extra fields.
    $form['#process'][] = array($this, 'processForm');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function processForm($element, $form_state, $form) {
    parent::processForm($element, $form_state, $form);

    $form_display = $this->getFormDisplay($form_state);

    // Assign the weights configured in the form display.
    // @todo: Once https://drupal.org/node/1875974 provides the missing API,
    //   only do it for 'extra fields', since other components have been taken
    //   care of in EntityViewDisplay::buildMultiple().
    foreach ($form_display->getComponents() as $name => $options) {
      if (isset($element[$name])) {
        $element[$name]['#weight'] = $options['weight'];
      }
    }

    // Hide extra fields.
    $extra_fields = field_info_extra_fields($this->entity->getEntityTypeId(), $this->entity->bundle(), 'form');
    foreach ($extra_fields as $extra_field => $info) {
      if (!$form_display->getComponent($extra_field)) {
        $element[$extra_field]['#access'] = FALSE;
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    $this->updateFormLangcode($form_state);
    $entity = $this->buildEntity($form, $form_state);
    $this->getFormDisplay($form_state)->validateFormValues($entity, $form, $form_state);

    // @todo Remove this.
    // Execute legacy global validation handlers.
    unset($form_state['validate_handlers']);
    form_execute_handlers('validate', $form, $form_state);
  }

  /**
   * Initialize the form state and the entity before the first form build.
   */
  protected function init(array &$form_state) {
    // Ensure we act on the translation object corresponding to the current form
    // language.
    $langcode = $this->getFormLangcode($form_state);
    $this->entity = $this->entity->getTranslation($langcode);

    $form_display = EntityFormDisplay::collectRenderDisplay($this->entity, $this->getOperation());
    $this->setFormDisplay($form_display, $form_state);

    parent::init($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormLangcode(array &$form_state) {
    if (empty($form_state['langcode'])) {
      // Imply a 'view' operation to ensure users edit entities in the same
      // language they are displayed. This allows to keep contextual editing
      // working also for multilingual entities.
      $form_state['langcode'] = $this->entityManager->getTranslationFromContext($this->entity)->language()->id;
    }
    return $form_state['langcode'];
  }

  /**
   * {@inheritdoc}
   */
  public function isDefaultFormLangcode(array $form_state) {
    return $this->getFormLangcode($form_state) == $this->entity->getUntranslated()->language()->id;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    $entity = clone $this->entity;

    // First, extract values from widgets.
    $extracted = $this->getFormDisplay($form_state)->extractFormValues($entity, $form, $form_state);

    // Then extract the values of fields that are not rendered through widgets,
    // by simply copying from top-level form values. This leaves the fields
    // that are not being edited within this form untouched.
    foreach ($form_state['values'] as $name => $values) {
      if ($entity->hasField($name) && !isset($extracted[$name])) {
        $entity->$name = $values;
      }
    }

    // Invoke all specified builders for copying form values to entity fields.
    if (isset($form['#entity_builders'])) {
      foreach ($form['#entity_builders'] as $function) {
        call_user_func_array($function, array($entity->getEntityTypeId(), $entity, &$form, &$form_state));
      }
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormDisplay(array $form_state) {
    return isset($form_state['form_display']) ? $form_state['form_display'] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setFormDisplay(EntityFormDisplayInterface $form_display, array &$form_state) {
    $form_state['form_display'] = $form_display;
    return $this;
  }

}
