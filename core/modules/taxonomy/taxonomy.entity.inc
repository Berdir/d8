<?php

/**
 * @file
 * Entity controller and class for comments.
 */

/**
 * Defines the comment entity class.
 */
class TaxonomyVocabulary extends Entity {

  /**
   * The taxonomy vocabulary ID.
   *
   * @var integer
   */
  public $vid;

  /**
   * Name of the vocabulary.
   *
   * @var string
   */
  public $name;

  /**
   * The vocabulary machine name.
   *
   * @var string
   */
  public $machine_name;

  /**
   * Description of the vocabulary.
   *
   * @var string
   */
  public $description;


  /**
   * The type of hierarchy allowed within the vocabulary.
   *
   * (0 = disabled, 1 = single, 2 = multiple)
   *
   * @var integer
   */
  public $hierarchy;

  /**
   * The weight of this vocabulary in relation to other vocabularies.'
   */
  public $weight;
}

/**
 * Defines the comment entity class.
 */
class TaxonomyTerm extends Entity {

  /**
   * The taxonomy term ID.
   *
   * @var integer
   */
  public $tid;

  /**
   * The taxonomy vocabulary ID this term belongs to.
   *
   * @var integer
   */
  public $vid;

  /**
   * Name of the vocabulary.
   *
   * @var string
   */
  public $name;

  /**
   * Description of the term.
   *
   * @var string
   */
  public $description;

  /**
   * The weight of this term in relation to other terms of the same vocabulary.'
   */
  public $weight;
}

/**
 * Controller class for taxonomy terms.
 *
 * This extends the DrupalDefaultEntityController class. Only alteration is
 * that we match the condition on term name case-independently.
 */
class TaxonomyTermController extends EntityDatabaseStorageController {

  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);
    $query->addTag('translatable');
    $query->addTag('term_access');
    // When name is passed as a condition use LIKE.
    if (isset($conditions['name'])) {
      $query_conditions = &$query->conditions();
      foreach ($query_conditions as $key => $condition) {
        if ($condition['field'] == 'base.name') {
          $query_conditions[$key]['operator'] = 'LIKE';
          $query_conditions[$key]['value'] = db_like($query_conditions[$key]['value']);
        }
      }
    }
    // Add the machine name field from the {taxonomy_vocabulary} table.
    $query->innerJoin('taxonomy_vocabulary', 'v', 'base.vid = v.vid');
    $query->addField('v', 'machine_name', 'vocabulary_machine_name');
    return $query;
  }

  protected function cacheGet($ids, $conditions = array()) {
    $terms = parent::cacheGet($ids, $conditions);
    // Name matching is case insensitive, note that with some collations
    // LOWER() and drupal_strtolower() may return different results.
    foreach ($terms as $term) {
      $term_values = (array) $term;
      if (isset($conditions['name']) && drupal_strtolower($conditions['name'] != drupal_strtolower($term_values['name']))) {
        unset($terms[$term->tid]);
      }
    }
    return $terms;
  }

  protected function postDelete($entities) {
    parent::postDelete($entities);

    // See if any of the term's children are about to be become orphans.
    $orphans = array();
    foreach (array_keys($entities) as $tid) {
      if ($children = taxonomy_get_children($tid)) {
        foreach ($children as $child) {
          // If the term has multiple parents, we don't delete it.
          $parents = taxonomy_get_parents($child->tid);
          // Because the parent has already been deleted, the parent count might
          // be 0.
          if (count($parents) <= 1) {
            $orphans[] = $child->tid;
          }
        }
      }
    }

    // Delete term hierarchy information and static caches after looking up
    // orphans but before deleting them so that their children/parent
    // information is consistent.
    db_delete('taxonomy_term_hierarchy')
      ->condition('tid', array_keys($entities))
      ->execute();
    taxonomy_terms_static_reset();

    if (!empty($orphans)) {
      debug($orphans, 'orphans');
      taxonomy_term_delete_multiple($orphans);
    }
  }

  protected function preSave(EntityInterface $entity) {
    parent::preSave($entity);

    // Prevent leading and trailing spaces in term names.
    $entity->name = trim($entity->name);
    if (!isset($entity->vocabulary_machine_name)) {
      $vocabulary = taxonomy_vocabulary_load($entity->vid);
      $entity->vocabulary_machine_name = $vocabulary->machine_name;
    }

   // Initialize parent array if not set for new terms.
    if ($entity->isNew() && !isset($entity->parent)) {
      $entity->parent = array(0);
    }
  }

  protected function postSave(EntityInterface $entity) {
    parent::postSave($entity);

    if (isset($entity->parent)) {
      // @todo: Drop support for non-array parent definition?
      if (!is_array($entity->parent)) {
        $entity->parent = array($entity->parent);
      }

      db_delete('taxonomy_term_hierarchy')
        ->condition('tid', $entity->tid)
        ->execute();

      $query = db_insert('taxonomy_term_hierarchy')
        ->fields(array('tid', 'parent'));
      foreach ($entity->parent as $parent) {
        if (is_array($parent)) {
          // @todo: Drop support for nested array parent definition?
          foreach ($parent as $tid) {
            $query->values(array(
              'tid' => $entity->tid,
              'parent' => $tid
            ));
          }
        }
        else {
          $query->values(array(
            'tid' => $entity->tid,
            'parent' => $parent
          ));
        }
      }
      $query->execute();
    }

    // Reset the taxonomy term static variables.
    taxonomy_terms_static_reset();
  }

}

/**
 * Controller class for taxonomy vocabularies.
 *
 * This extends the DrupalDefaultEntityController class, adding required
 * special handling for taxonomy vocabulary objects.
 */
class TaxonomyVocabularyController extends EntityDatabaseStorageController {

  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);
    $query->addTag('translatable');
    $query->orderBy('base.weight');
    $query->orderBy('base.name');
    return $query;
  }

  protected function preSave(EntityInterface $entity) {
    parent::preSave($entity);
    // Prevent leading and trailing spaces in vocabulary names.
    if (!empty($entity->name)) {
      $entity->name = trim($entity->name);
    }
  }

  protected function postSave(EntityInterface $entity) {
    parent::postSave($entity);
    cache_clear_all();
    taxonomy_vocabulary_static_reset();
    // @todo: Check if there is a better way to detect if this is an update or
    // an insert.
    if (!isset($entity->original)) {
      field_attach_create_bundle('taxonomy_term', $entity->machine_name);
    }
    elseif ($entity->original->machine_name != $entity->machine_name) {
      field_attach_rename_bundle('taxonomy_term', $entity->original->machine_name, $entity->machine_name);
    }
  }

  protected function preDelete($entities) {
    parent::preDelete($entities);

    // Only load terms without a parent, child terms will get deleted too.
    $tids = db_query('SELECT t.tid FROM {taxonomy_term_data} t INNER JOIN {taxonomy_term_hierarchy} th ON th.tid = t.tid WHERE t.vid IN (:vids) AND th.parent = 0', array(':vids' => array_keys($entities)))->fetchCol();
    debug($tids);
    taxonomy_term_delete_multiple($tids);
  }

  protected function postDelete($entities) {
    parent::postDelete($entities);
    taxonomy_vocabulary_static_reset();
  }
}
