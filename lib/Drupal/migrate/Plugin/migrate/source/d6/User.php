<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\User.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;

/**
 * Drupal 6 user source from database.
 *
 * @PluginId("drupal6_user")
 */
class User extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('users', 'u')
      ->fields('u', array('uid', 'name', 'pass', 'mail', 'mode', 'sort', 'threshold', 'theme', 'signature', 'signature_format', 'created', 'access', 'login', 'status', 'timezone', 'language', 'picture', 'init', 'data', 'timezone'))
      ->condition('uid', 0, '>');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'uid' => t('User ID'),
      'name' => t('Username'),
      'pass' => t('Password'),
      'mail' => t('Email address'),
      'mode' => t('Per-user comment display mode'),
      'sort' => t('Per-user comment sort order'),
      'threshold' => t('Obsolete comment configuration'),
      'theme' => t('Default theme'),
      'signature' => t('Signature'),
      'signature_format' => t('Signature format'),
      'created' => t('Registered timestamp'),
      'access' => t('Last access timestamp'),
      'login' => t('Last login timestamp'),
      'status' => t('Status'),
      'timezone' => t('Timezone'),
      'language' => t('Language'),
      'picture' => t('Picture'),
      'init' => t('Init'),
      'data' => t('Data'),
    );
  }

}
