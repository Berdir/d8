<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\User.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 user source from database.
 *
 * @PluginID("drupal6_user")
 */
class User extends Drupal6SqlBase implements SourceEntityInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 1, '>');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();

    if ($this->moduleExists('profile')) {
        // Profile fields.
      $fields += $this->select('profile_fields', 'pf')
        ->fields('pf', array('name', 'title'))
        ->execute()
        ->fetchAllKeyed();
    }

    return $fields;
  }

  function prepareRow(Row $row, $keep = TRUE) {
    // We are adding here the Event contributed module column.
    // @see https://api.drupal.org/api/drupal/modules%21user%21user.install/function/user_update_7002/7
    if ($row->hasSourceProperty('timezone_id') && $row->getSourceProperty('timezone_id')) {
      if ($this->getDatabase()->schema()->tableExists('event_timezones')) {
        $event_timezone = $this->getDatabase()
          ->select('event_timezones', 'e')
          ->fields('e', array('name'))
          ->condition('e.timezone', $row->getSourceProperty('timezone_id'))
          ->execute()
          ->fetchField();
        if ($event_timezone) {
          $row->setSourceProperty('event_timezone', $event_timezone);
        }
      }
    }

    if ($this->moduleExists('profile')) {
      // Find profile values for this row.
      $query = $this->select('profile_values', 'pv', array('fetch' => \PDO::FETCH_ASSOC))
        ->fields('pv', array('fid', 'value'));
      $query->leftJoin('profile_fields', 'pf', 'pf.fid=pv.fid');
      $query->fields('pf', array('name', 'type'));
      $query->condition('uid', $row->getSourceProperty('uid'));
      $results = $query->execute();

      foreach ($results as $profile_value) {
        // Check special case for date. We need unserialize.
        if ($profile_value['type'] == 'date') {
          $date = unserialize($profile_value['value']);
          $date = date('Y-m-d', mktime(0, 0, 0, $date['month'], $date['day'], $date['year']));
          $row->setSourceProperty($profile_value['name'], array('value' => $date));
        }
        else {
          $row->setSourceProperty($profile_value['name'], array($profile_value['value']));
        }
      }
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
  }

  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = array(
      'uid' => t('User ID'),
      'name' => t('Username'),
      'pass' => t('Password'),
      'mail' => t('Email address'),
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
    );

    // Possible field added by Date contributed module.
    // @see https://api.drupal.org/api/drupal/modules%21user%21user.install/function/user_update_7002/7
    if ($this->getDatabase()->schema()->fieldExists('users', 'timezone_name')) {
      $fields['timezone_name'] = t('Timezone (Date)');
    }

    // Possible field added by Event contributed module.
    // @see https://api.drupal.org/api/drupal/modules%21user%21user.install/function/user_update_7002/7
    if ($this->getDatabase()->schema()->fieldExists('users', 'timezone_id')) {
      $fields['timezone_id'] = t('Timezone (Event)');
    }

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }

}
