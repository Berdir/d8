<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\source\d6\Taxonomy.
 */

namespace Drupal\migrate\Plugin\migrate\source\d6;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 6 taxonomy source.
 *
 * @PluginId("drupal6_taxonomy")
 */
class Taxonomy extends SqlBase {

  /**
   * The machine name, or vocabulary ID (pre-D7), of the vocabulary we're
   * migrating from.
   *
   * @var mixed
   */
  protected $sourceVocabulary;

  function query() {

    // @todo: determine how to pass in arguments via plugin config constructor
    $this->sourceVocabulary = $this->configuration['vocabulary'];

    // Note the explode - this supports the (admittedly unusual) case of
    // consolidating multiple vocabularies into one.
    $query = $this->database
      ->select('term_data', 'td')
      ->fields('td', array('tid', 'vid', 'name', 'description', 'weight'))
      ->condition('vid', explode(',', $this->sourceVocabulary), 'IN')
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
   * Derived classes must implement fields(), returning a list of available
   * source fields.
   *
   * @return array
   *   Keys: machine names of the fields (to be passed to addFieldMapping)
   *   Values: Human-friendly descriptions of the fields.
   */
  public function fields() {
    // TODO: Implement fields() method.
  }

}
