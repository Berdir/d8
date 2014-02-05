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
 * @PluginID("drupal6_taxonomy_vocabulary_per_type")
 */
class VocabularyPerType extends Vocabulary implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->fields('nt', array(
        'type',
      ));
    $query->join('vocabulary_node_types', 'nt', 'v.vid = nt.vid');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['vid']['type'] = 'integer';
    $ids['vid']['alias'] = 'nt';
    $ids['type']['type'] = 'string';
    return $ids;
  }

}
