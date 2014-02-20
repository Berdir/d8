<?php

/**
 * @file
 * Contains \Drupal\path\Plugin\Field\FieldWidget\PathWidget.
 */

namespace Drupal\path\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Language\Language;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'path' widget.
 *
 * @FieldWidget(
 *   id = "path",
 *   label = @Translation("Path alias"),
 *   field_types = {
 *     "path"
 *   },
 *   settings = {
 *   }
 * )
 */
class PathWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    $entity = $items->getEntity();
    $path = array();
    if (!$entity->isNew()) {
      $conditions = array('source' => $entity->getSystemPath());
      if ($entity->language()->id != Language::LANGCODE_NOT_SPECIFIED) {
        $conditions['langcode'] = $entity->language()->id;
      }
      $path = \Drupal::service('path.crud')->load($conditions);
      if ($path === FALSE) {
        $path = array();
      }
    }
    $path += array(
      'pid' => NULL,
      'source' => $entity->id() ? $entity->getSystemPath() : NULL,
      'alias' => '',
      'langcode' => $entity->language()->id,
    );

    $account = \Drupal::currentUser();
    $element = array(
      '#type' => 'details',
      '#title' => t('URL path settings'),
      '#collapsed' => empty($path['alias']),
      '#attributes' => array(
        'class' => array('path-form'),
      ),
      '#attached' => array(
        'library' => array(array('path', 'drupal.path')),
      ),
      '#access' => $account->hasPermission('create url aliases') || $account->hasPermission('administer url aliases'),
      '#element_validate' => array('path_form_element_validate'),
    );
    $element['alias'] = array(
      '#type' => 'textfield',
      '#title' => t('URL alias'),
      '#default_value' => $path['alias'],
      '#maxlength' => 255,
      '#description' => t('The alternative URL for this content. Use a relative path without a trailing slash. For example, enter "about" for the about page.'),
    );
    $element['pid'] = array('#type' => 'value', '#value' => $path['pid']);
    $element['source'] = array('#type' => 'value', '#value' => $path['source']);
    $element['langcode'] = array('#type' => 'value', '#value' => $path['langcode']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, array &$form_state) {
    return $element['path'];
  }

}
