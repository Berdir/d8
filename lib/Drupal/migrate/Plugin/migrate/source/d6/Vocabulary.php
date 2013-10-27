<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Vocabulary.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 vocabularies source from database.
 *
 * @PluginId("drupal6_vocabulary")
 */
class Vocabulary extends SqlBase {

  /**
   * {@inheritdoc}
   */
  function query() {
    $query = $this->database
      ->select('vocabulary', 'v')
      ->fields('v', array(
        'vid',
        'name',
        'description',
        'help',
        'relations',
        'hierarchy',
        'multiple',
        'required',
        'tags',
        'module',
        'modified',
        'weight',
      ));
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // @fixme Implement.
  }

}
