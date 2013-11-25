<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\UserPicture.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;


/**
 * Drupal 6 user picture source from database.
 *
 * @todo Support default picture?
 *
 * @PluginId("drupal6_user_picture")
 */
class UserPicture extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('users', 'u')
      ->condition('picture', '', '<>')
      ->fields('u', array('uid', 'access', 'picture'))
      ->orderBy('access');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'uid' => 'Primary Key: Unique user ID.',
      'access' => 'Timestamp for previous time user accessed the site.',
      'picture' => "Path to the user's uploaded picture.",
    );
  }

}
