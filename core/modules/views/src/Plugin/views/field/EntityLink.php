<?php

/**
 * @file
 * Contains \Drupal\views\Plugin\views\field\EntityLink.
 */

namespace Drupal\views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Field handler to present a link to the entity_link.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_link")
 */
class EntityLink extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    // @FIXME
    // $this->additional_fields['langcode'] = ['table' => 'node_field_data', 'field' => 'langcode'];
  }

  /**
   * {@inheritdoc}
   */
  public function usesGroupBy() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['text'] = ['default' => '', 'translatable' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Text to display'),
      '#default_value' => $this->options['text'],
    ];
    parent::buildOptionsForm($form, $form_state);

    // The path is set by renderLink function so don't allow to set it.
    $form['alter']['path'] = ['#access' => FALSE];
    $form['alter']['external'] = ['#access' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->addAdditionalFields();
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if ($entity = $this->getEntity($values)) {
      return $this->renderLink($entity, $values);
    }
  }

  /**
   * Prepares the link to view a entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The node entity this field belongs to.
   * @param \Drupal\views\ResultRow $values
   *   The values retrieved from the view's result set.
   *
   * @return string
   *   Returns a string for the link text.
   */
  protected function renderLink($entity, ResultRow $values) {
    if ($entity->access('view')) {
      $this->options['alter']['make_link'] = TRUE;
      $this->options['alter']['path'] = $entity->getSystemPath();

      $this->addLangcode($values);

      $text = !empty($this->options['text']) ? $this->options['text'] : $this->t('view');
      return $text;
    }
  }

  /**
   * @param \Drupal\views\ResultRow $values
   */
  protected function addLangcode(ResultRow $values) {
    if (isset($this->aliases['langcode'])) {
      $languages = language_list();
      $langcode = $this->getValue($values, 'langcode');
      if (isset($languages[$langcode])) {
        $this->options['alter']['language'] = $languages[$langcode];
      }
      else {
        unset($this->options['alter']['language']);
      }
    }
  }
}
