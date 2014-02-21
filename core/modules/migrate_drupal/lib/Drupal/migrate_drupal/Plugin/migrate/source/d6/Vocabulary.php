<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Vocabulary.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;

use Drupal\migrate\Row;

/**
 * Drupal 6 vocabularies source from database.
 *
 * @MigrateSource("d6_taxonomy_vocabulary")
 */
class Vocabulary extends VocabularyBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Find node types for this row.
    $node_types = $this->select('vocabulary_node_types', 'nt')
      ->fields('nt', array('type', 'vid'))
      ->condition('vid', $row->getSourceProperty('vid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('node_types', $node_types);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['vid']['type'] = 'integer';
    return $ids;
  }

}
