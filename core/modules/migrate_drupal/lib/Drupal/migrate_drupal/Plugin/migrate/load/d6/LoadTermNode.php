<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\load\d6\TermNode.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\load\d6;

use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\load\LoadEntity;

/**
 * @PluginID("d6_term_node")
 */
class LoadTermNode extends LoadEntity {

  /**
   * @var array
   */
  protected $vocabularyMap;

  /**
   * {@inheritdoc}
   */
  protected function processIdMap($id_map) {
    parent::processIdMap($id_map);
    $this->vocabularyMap = array_combine($this->bundles, iterator_to_array($id_map));
  }

  /**
   * {@inheritdoc}
   */
  protected function additionalProcess($id, MigrationInterface $migration) {
    $migration->process[$this->vocabularyMap[$id]['destid2']] = 'tid';
    parent::additionalProcess($id, $migration);
  }

}
