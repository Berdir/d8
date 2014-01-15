<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Role.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;


use Drupal\migrate\Row;

/**
 * Drupal 6 role source from database.
 *
 * @PluginId("drupal6_user_role")
 */
class Role extends Drupal6SqlBase {

  /**
   * List of filter IDs per role IDs.
   *
   * @var array
   */
  protected $filterPermissions = array();

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('role', 'r')
      ->fields('r', array('rid', 'name'))
      ->orderBy('rid');
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

  /**
   * {@inheritdoc}
   */
  protected function runQuery() {
    $filter_roles = $this->getDatabase()->query('SELECT format, roles FROM {filter_formats}')->fetchAllKeyed();
    foreach ($filter_roles as $format => $roles) {
      // Drupal 6 code: $roles = ','. implode(',', $roles) .',';
      // Remove the beginning and ending comma.
      foreach (explode(',', trim($roles, ',')) as $rid) {
        $this->filterPermissions[$rid][] = $format;
      }
    }
    return parent::runQuery();
  }

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row, $keep = TRUE) {
    $rid = $row->getSourceProperty('rid');
    $permissions = $this->getDatabase()
      ->query('SELECT perm FROM {permission} WHERE rid = :rid', array(':rid' => $rid))
      ->fetchField();
    $row->setSourceProperty('permissions', explode(', ', $permissions));
    if (isset($this->filterPermissions[$rid])) {
      $row->setSourceProperty("filter_permissions:$rid", $this->filterPermissions[$rid]);
    }
    return parent::prepareRow($row);
  }

}
