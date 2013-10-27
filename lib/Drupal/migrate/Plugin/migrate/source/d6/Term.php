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
   * The vocabulary ID.
   *
   * As mentioned in query(), this can be a comma separated list of vocabulary
   * ids.
   *
   * @var mixed
   */
  protected $vocabulary;

  /**
   * {@inheritdoc}
   */
  function __construct(array $configuration, $plugin_id, array $plugin_definition, MigrationInterface $migration, CacheBackendInterface $cache, KeyValueStoreInterface $highwater_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $cache, $highwater_storage);
    if (empty($configuration['vocabulary'])) {
      // @todo MigrateException?
      throw new \Exception('At least one vocabulary ID is required to instanciate a D6 Term source.');
    }
    $this->vocabulary = $configuration['vocabulary'];
  }

  /**
   * {@inheritdoc}
   */
  function query() {
    // Note the explode - this supports the (admittedly unusual) case of
    // consolidating multiple vocabularies into one.
    $query = $this->database
      ->select('term_data', 'td')
      ->fields('td', array('tid', 'vid', 'name', 'description', 'weight'))
      ->condition('vid', explode(',', $this->vocabulary), 'IN')
      // @todo: working, but not is there support for distinct() in FakeSelect?
      ->distinct();
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
