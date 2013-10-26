<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Action.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 action source from database.
 *
 * @PluginId("drupal6_action")
 */
class Action extends SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('actions', 'a')
      ->fields('a', array('aid', 'type', 'callback', 'parameters', 'description'));
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'aid' => t('Action ID'),
      'type' => t('Module'),
      'callback' => t('Callback function'),
      'parameters' => t('Action configuration'),
      'description' => t('Action description'),
    );
  }

}
