<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Plugin\migrate\source\d6\TermNode.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\migrate\Plugin\SourceEntityInterface;
use Drupal\migrate\Row;

/**
 * Source returning tids from the term_node table for the current revision.
 *
 * @MigrateSource(
 *   id = "d6_term_node"
 * )
 */
class TermNode extends Drupal6SqlBase implements SourceEntityInterface, RequirementsInterface {

    /**
   * The join options between the node and the term node table.
   */
  const JOIN = 'tn.vid = n.vid';

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('term_node', 'tn')
      // @todo: working, but not is there support for distinct() in FakeSelect?
      ->distinct()
      ->fields('tn', array('nid', 'vid'));
    // Because this is an inner join it enforces the current revision.
    $query->innerJoin('node', 'n', static::JOIN);
    return $query;

  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'nid' => $this->t('The node revision ID.'),
      'vid' => $this->t('The node revision ID.'),
      'tid' => $this->t('The term ID.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Select the terms belonging to the revision selected.
    $query = $this->select('term_node', 'tn')
      ->fields('tn', array('tid'))
      ->condition('n.nid', $row->getSourceProperty('nid'));
    $query->join('node', 'n', static::JOIN);
    $row->setSourceProperty('tid', $query->execute()->fetchCol());
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('taxonomy');
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['vid']['type'] = 'integer';
    $ids['vid']['alias'] = 'tn';
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'taxonomy_term';
  }
}
