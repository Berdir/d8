<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Term.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\d6\Drupal6SqlBase;
use Drupal\migrate\Plugin\RequirementsInterface;
use Drupal\migrate\Row;

/**
 * Drupal 6 taxonomy terms source from database.
 *
 * @todo Support term_relation, term_synonym table if possible.
 *
 * @PluginId("drupal6_term")
 */
class Term extends Drupal6SqlBase implements RequirementsInterface {

  /**
   * {@inheritdoc}
   */
  function query() {
    // Note the explode - this supports the (admittedly unusual) case of
    // consolidating multiple vocabularies into one.
    $query = $this->database
      ->select('term_data', 'td')
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
      'parents' => t("The Drupal term IDs of the term's parents."),
    );
  }

  /**
   * {@inheritdoc}
   */
  function prepareRow(Row $row) {
    // Find parents for this row.
    $parents = $this->database
      ->select('term_hierarchy', 'th')
      ->fields('th', array('parent', 'tid'))
      ->condition('tid', $row->getSourceProperty('tid'))
      ->execute()
      ->fetchCol();
    $row->setSourceProperty('parents', $parents);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function checkRequirements() {
    return $this->moduleExists('taxonomy');
  }

}
