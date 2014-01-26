<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Term.
 */

namespace Drupal\migrate_drupal\Plugin\migrate\source\d6;


use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 taxonomy terms source from database.
 *
 * @todo Support term_relation, term_synonym table if possible.
 *
 * @PluginID("drupal6_taxonomy_term")
 */
class Term extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Note the explode - this supports the (admittedly unusual) case of
    // consolidating multiple vocabularies into one.
    $query = $this->select('term_data', 'td')
      ->fields('td', array('tid', 'vid', 'name', 'description', 'weight'))
      // @todo: working, but not is there support for distinct() in FakeSelect?
      ->distinct();
    if (isset($this->configuration['vocabulary'])) {
      $query->condition('vid', $this->configuration['vocabulary'], 'IN');
    }
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'tid' => t('The term ID.'),
      'vid' => t('Existing term VID'),
      'name' => t('The name of the term.'),
      'description' => t('The term description.'),
      'weight' => t('Weight'),
      'parent' => t("The Drupal term IDs of the term's parents."),
    );
  }

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row) {
    // Find parents for this row.
    $parents = $this->select('term_hierarchy', 'th')
      ->fields('th', array('parent', 'tid'))
      ->condition('tid', $row->getSourceProperty('tid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('parent', $parents);
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
    $ids['vid']['type'] = 'string';
    return $ids;
  }

}
