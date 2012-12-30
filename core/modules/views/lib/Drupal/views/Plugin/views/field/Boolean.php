<?php

/**
 * @file
 * Definition of Drupal\views\Plugin\views\field\Boolean.
 */

namespace Drupal\views\Plugin\views\field;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\Core\Annotation\Plugin;

/**
 * A handler to provide proper displays for booleans.
 *
 * Allows for display of true/false, yes/no, on/off, enabled/disabled.
 *
 * Definition terms:
 *   - output formats: An array where the first entry is displayed on boolean true
 *      and the second is displayed on boolean false. An example for sticky is:
 *      @code
 *      'output formats' => array(
 *        'sticky' => array(t('Sticky'), ''),
 *      ),
 *      @endcode
 *
 * @ingroup views_field_handlers
 *
 * @Plugin(
 *   id = "boolean"
 * )
 */
class Boolean extends FieldPluginBase {

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['type'] = array('default' => 'yes-no');
    $options['not'] = array('definition bool' => 'reverse');

    return $options;
  }

  /**
   * Overrides \Drupal\views\Plugin\views\field\FieldPluginBase::init().
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $default_formats = array(
      'yes-no' => array(t('Yes'), t('No')),
      'true-false' => array(t('True'), t('False')),
      'on-off' => array(t('On'), t('Off')),
      'enabled-disabled' => array(t('Enabled'), t('Disabled')),
      'boolean' => array(1, 0),
      'unicode-yes-no' => array('✔', '✖'),
    );
    $output_formats = isset($this->definition['output formats']) ? $this->definition['output formats'] : array();
    $this->formats = array_merge($default_formats, $output_formats);
  }

  public function buildOptionsForm(&$form, &$form_state) {
    foreach ($this->formats as $key => $item) {
      $options[$key] = implode('/', $item);
    }

    $form['type'] = array(
      '#type' => 'select',
      '#title' => t('Output format'),
      '#options' => $options,
      '#default_value' => $this->options['type'],
    );
    $form['not'] = array(
      '#type' => 'checkbox',
      '#title' => t('Reverse'),
      '#description' => t('If checked, true will be displayed as false.'),
      '#default_value' => $this->options['not'],
    );
    parent::buildOptionsForm($form, $form_state);
  }

  function render($values) {
    $value = $this->get_value($values);
    if (!empty($this->options['not'])) {
      $value = !$value;
    }

    if (isset($this->formats[$this->options['type']])) {
      return $value ? $this->formats[$this->options['type']][0] : $this->formats[$this->options['type']][1];
    }
    else {
      return $value ? $this->formats['yes-no'][0] : $this->formats['yes-no'][1];
    }
  }

}
