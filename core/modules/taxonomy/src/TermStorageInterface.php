<?php

/**
 * @file
 * Contains \Drupal\taxonomy\TermStorageInterface.
*/

namespace Drupal\taxonomy;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines an interface for taxonomy_term entity storage classes.
 */
interface TermStorageInterface extends EntityStorageInterface {

  /**
   * Finds all ancestors of a given term ID.
   *
   * @param int $tid
   *   Term ID to retrieve ancestors for.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   An array of term objects which are the ancestors of the term $tid.
   */
  public function loadAllParents($tid);

  /**
   * Finds all terms in a given vocabulary ID.
   *
   * @param string $vid
   *   Vocabulary ID to retrieve terms for.
   * @param int $parent
   *   The term ID under which to generate the tree. If 0, generate the tree
   *   for the entire vocabulary.
   * @param int $max_depth
   *   The number of levels of the tree to return. Leave NULL to return all
   *   levels.
   * @param bool $load_entities
   *   If TRUE, a full entity load will occur on the term objects. Otherwise
   *   they are partial objects queried directly from the {taxonomy_term_data}
   *   table to save execution time and memory consumption when listing large
   *   numbers of terms. Defaults to FALSE.
   *
   * @return \Drupal\taxonomy\TermInterface[]
   *   An array of term objects that are the children of the vocabulary $vid.
   */
  public function loadTree($vid, $parent = 0, $max_depth = NULL, $load_entities = FALSE);

  /**
   * Count the number of nodes in a given vocabulary ID.
   *
   * @param string $vid
   *   Vocabulary ID to retrieve terms for.
   *
   * @return int
   *   A count of the nodes in a given vocabulary ID.
   */
  public function nodeCount($vid);

  /**
   * Reset the weights for a given vocabulary ID.
   *
   * @param string $vid
   *   Vocabulary ID to retrieve terms for.
   */
  public function resetWeights($vid);

  /**
   * Returns all terms used to tag some given nodes.
   *
   * @param array $nids
   *   Node IDs to retrieve terms for.
   * @param array $vocabs
   *   (optional) A vocabularies array to restrict the term search. Defaults to
   *   empty array.
   * @param string $langcode
   *   (optional) A language code to restrict the term search. Defaults to NULL.
   *
   * @return array
   *   An array of nids and the term entities they were tagged with.
   */
  public function getNodeTerms($nids, $vocabs = array(), $langcode = NULL);

}
