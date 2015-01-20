<?php

/**
 * @file
 * Contains \Drupal\options\Plugin\views\argument\TextListField.
 */

namespace Drupal\options\Plugin\views\argument;

use Drupal\Core\Field\AllowedTagsXssTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Views\FieldStorageViewsTrait;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\argument\String;

/**
 * Argument handler for list field to show the human readable name in summary.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("text_list_field")
 */
class TextListField extends String {

  use AllowedTagsXssTrait;
  use FieldStorageViewsTrait;

  /**
   * Stores the allowed values of this field.
   *
   * @var array
   */
  protected $allowedValues = NULL;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $field = $this->getFieldStorageDefinition();
    $this->allowedValues = options_allowed_values($field);
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['summary']['contains']['human'] = ['default' => FALSE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['summary']['human'] = [
      '#title' => $this->t('Display list value as human readable'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['summary']['human'],
      '#states' => [
        'visible' => [
          ':input[name="options[default_action]"]' => ['value' => 'summary'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function summaryName($data) {
    $value = $data->{$this->name_alias};
    // If the list element has a human readable name show it.
    if (isset($this->allowedValues[$value]) && !empty($this->options['summary']['human'])) {
      return $this->caseTransform($this->fieldFilterXss($this->allowedValues[$value]), $this->options['case']);
    }
    // Else, fallback to the key.
    else {
      return $this->caseTransform(String::checkPlain($value), $this->options['case']);
    }
  }

}
