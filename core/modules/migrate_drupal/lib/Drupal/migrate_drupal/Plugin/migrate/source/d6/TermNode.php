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
 * Drupal 6 taxonomy term-node relationship source from database.
 *
 * @PluginID("drupal6_term_node")
 */
class TermNode extends Drupal6SqlBase implements SourceEntityInterface, RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select only the current revision -- adding vid to the fields would
    // make the distinct pointless so only select the nodes.
    $query = $this->select('term_node', 'tn')
      // @todo: working, but not is there support for distinct() in FakeSelect?
      ->distinct()
      ->fields('tn', array('nid'));
    $query->join('node', 'n', 'tn.vid = n.vid');
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
  function prepareRow(Row $row) {
    // Select the terms belonging the current revision, see query() why vid is
    // not available.
    $query = $this->select('term_node', 'tn')
      ->fields('tn', array('tid'))
      ->condition('n.nid', $row->getSourceProperty('nid'));
    $query->join('node', 'n', 'tn.vid = n.vid');
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
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'tn';
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
