<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Action.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

/**
 * Drupal 6 action source from database.
 *
 * @PluginID("drupal6_action")
 */
class Action extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('actions', 'a')
      ->fields('a', array(
        'aid',
        'type',
        'callback',
        'parameters',
        'description',
      )
    );
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'aid' => $this->t('Action ID'),
      'type' => $this->t('Module'),
      'callback' => $this->t('Callback function'),
      'parameters' => $this->t('Action configuration'),
      'description' => $this->t('Action description'),
    );
  }

}
