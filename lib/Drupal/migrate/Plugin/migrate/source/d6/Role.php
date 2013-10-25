<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Role.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 role source from database.
 *
 * @PluginId("drupal6_role")
 */
class Role extends SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('role', 'r')
      ->fields('r', array('rid', 'name'));
    $query->orderBy('rid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'rid' => t('Role ID.'),
      'name' => t('The name of the user role.'),
    );
  }

}
