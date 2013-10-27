<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Term.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\migrate\Entity\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 taxonomy terms source from database.
 *
 * @todo Support term_relation, term_synonym table if possible.
 *
 * @PluginId("drupal6_term")
 */
class Term extends SqlBase {

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
    // Join to the hierarchy so we can sort on parent, but we'll pull the
    // actual parent values in separately in case there are multiples.
    $query->leftJoin('term_hierarchy', 'th', 'td.tid = th.tid');
    $query->fields('th', array('parent'));
    $query->orderBy('parent');
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
      'parent' => t("The Drupal term ID of the term's parent."),
    );
  }

}
