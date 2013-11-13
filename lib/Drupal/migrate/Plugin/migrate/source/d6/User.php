<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\User.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;


use Drupal\migrate\Row;

/**
 * Drupal 6 user source from database.
 *
 * @PluginId("drupal6_user")
 */
class User extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
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
    //TODO: Added profile fields if module profile is enable.
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

  function prepareRow(Row $row, $keep = TRUE) {
    if ($this->moduleExists('profile')) {
      // Find profile values for this row.
      $query = $this->database
        ->select('profile_values', 'pv', array('fetch' => \PDO::FETCH_ASSOC))
        ->fields('pv', array('fid', 'value'));
      $query->leftJoin('profile_fields', 'pf', 'pf.fid=pv.fid');
      $query->fields('pf', array('name', 'type'));
      $query->condition('uid', $row->getSourceProperty('uid'));
      $results = $query->execute();

      foreach ($results as $profile_value) {
        //Check special case for date. We need unserialize.
        if ($profile_value['type'] == 'date') {
          $row->setSourceProperty($profile_value['name'], array(unserialize($profile_value['value'])));
        }
        else {
          $row->setSourceProperty($profile_value['name'], array($profile_value['value']));
        }
      }
    }
    return parent::prepareRow($row);
  }

}
