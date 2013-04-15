<?php

/**
 * @file
 * Definition of Drupal\views\Plugin\views\argument\Date.
 */

namespace Drupal\views\Plugin\views\argument;

use Drupal\Component\Annotation\PluginID;
use Drupal\Core\Database\Database;

/**
 * Abstract argument handler for dates.
 *
 * Adds an option to set a default argument based on the current date.
 *
 * Definitions terms:
 * - many to one: If true, the "many to one" helper will be used.
 * - invalid input: A string to give to the user for obviously invalid input.
 *                  This is deprecated in favor of argument validators.
 *
 * @see Drupal\views\ManyTonOneHelper
 *
 * @ingroup views_argument_handlers
 *
 * @PluginID("date")
 */
class Date extends Formula {

  /**
   * The date format used in the title.
   *
   * @var string
   */
  protected $format;

  /**
   * The date format used in the query.
   *
   * @var string
   */
  protected $argFormat = 'Y-m-d';

  var $option_name = 'default_argument_date';

  /**
   * Add an option to set the default value to the current date.
   */
  function default_argument_form(&$form, &$form_state) {
    parent::default_argument_form($form, $form_state);
    $form['default_argument_type']['#options'] += array('date' => t('Current date'));
    $form['default_argument_type']['#options'] += array('node_created' => t("Current node's creation time"));
    $form['default_argument_type']['#options'] += array('node_changed' => t("Current node's update time"));  }

  /**
   * Set the empty argument value to the current date,
   * formatted appropriately for this argument.
   */
  function get_default_argument($raw = FALSE) {
    if (!$raw && $this->options['default_argument_type'] == 'date') {
      return date($this->argFormat, REQUEST_TIME);
    }
    elseif (!$raw && in_array($this->options['default_argument_type'], array('node_created', 'node_changed'))) {
      foreach (range(1, 3) as $i) {
        $node = menu_get_object('node', $i);
        if (!empty($node)) {
          continue;
        }
      }

      if (arg(0) == 'node' && is_numeric(arg(1))) {
        $node = node_load(arg(1));
      }

      if (empty($node)) {
        return parent::get_default_argument();
      }
      elseif ($this->options['default_argument_type'] == 'node_created') {
        return date($this->argFormat, $node->created);
      }
      elseif ($this->options['default_argument_type'] == 'node_changed') {
        return date($this->argFormat, $node->changed);
      }
    }

    return parent::get_default_argument($raw);
  }

  function get_sort_name() {
    return t('Date', array(), array('context' => 'Sort order'));
  }

  /**
   * Overrides \Drupal\views\Plugin\views\argument\Formula::get_formula().
   */
  function get_formula() {
    $this->formula = $this->getDateFormat($this->argFormat);
    return parent::get_formula();
  }

}
