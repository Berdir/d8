<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\FilterFormats.
 */

namespace Drupal\migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;
use Drupal\migrate\Row;

/**
 * Drupal 6 role source from database.
 *
 * @PluginId("drupal6_filter_formats")
 */
class D6FilterFormats extends Drupal6SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->database
      ->select('filter_formats', 'f')
      ->fields('f', array('format', 'name', 'roles', 'cache'));
    $query->orderBy('format');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'format' => t('Format ID.'),
      'name' => t('The name of the filter format.'),
      'roles' => t('The user roles that can use the format.'),
      'cache' => t('Flag to indicate whether format is cachable. (1 = cachable, 0 = not cachable).'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $filters = array();
    // Find filters for this row.
    $results = $this->database
      ->select('filters', 'f', array('fetch' => \PDO::FETCH_ASSOC))
      ->fields('f', array('fid', 'format', 'module', 'delta', 'weight'))
      ->condition('format', $row->getSourceProperty('format'))
      ->execute();
    foreach ($results as $filter) {
      $filters[] = array(
        'fid' => $filter['fid'],
        'module' => $filter['module'],
        'delta' => $filter['delta'],
        'weight' => $filter['weight'],
      );
    }

    $row->setSourceProperty('filters', $filters);
    return parent::prepareRow($row);
  }
}
