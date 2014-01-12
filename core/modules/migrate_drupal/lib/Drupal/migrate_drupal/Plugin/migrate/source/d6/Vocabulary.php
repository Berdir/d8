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
 * @PluginId("drupal6_taxonomy_vocabulary")
 */
class Vocabulary extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('vocabulary', 'v')
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
        'weight',
      ));
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'vid' => t('The vocabulary ID.'),
      'name' => t('The name of the vocabulary.'),
      'description' => t('The description of the vocabulary.'),
      'help' => t('Help text to display for the vocabulary.'),
      'relations' => t('Whether or not related terms are enabled within the vocabulary. (0 = disabled, 1 = enabled)'),
      'hierarchy' => t('The type of hierarchy allowed within the vocabulary. (0 = disabled, 1 = single, 2 = multiple)'),
      'multiple' => t('Whether or not multiple terms from this vocabulary may be assigned to a node. (0 = disabled, 1 = enabled)'),
      'required' => t('Whether or not terms are required for nodes using this vocabulary. (0 = disabled, 1 = enabled)'),
      'tags' => t('Whether or not free tagging is enabled for the vocabulary. (0 = disabled, 1 = enabled)'),
      'weight' => t('The weight of the vocabulary in relation to other vocabularies.'),
      'parents' => t("The Drupal term IDs of the term's parents."),
      'node_types' => t('The names of the node types the vocabulary may be used with.'),
    );
  }

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
  public function checkRequirements() {
    return $this->moduleExists('taxonomy');
  }

}
