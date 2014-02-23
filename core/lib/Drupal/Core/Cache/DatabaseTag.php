<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\DatabaseTag.
 */

namespace Drupal\Core\Cache;

use Drupal\Core\Database\Connection;

/**
 * Class DatabaseTag.
 */
class DatabaseTag extends CacheTagBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a DatabaseTag object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    $tag_cache = &drupal_static('Drupal\Core\Cache\CacheTagInterface::tagCache', array());
    try {
      foreach ($this->flattenTags($tags) as $tag) {
        unset($tag_cache[$tag]);
        $this->connection->merge('cache_tags')
          ->insertFields(array('invalidations' => 1))
          ->expression('invalidations', 'invalidations + 1')
          ->key(array('tag' => $tag))
          ->execute();
      }
    }
    catch (\Exception $e) {
      $this->catchException($e, 'cache_tags');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTags(array $tags) {
    $deleted_tags = &drupal_static('Drupal\Core\Cache\DatabaseTag::deletedTags', array());
    $tag_cache = &drupal_static('Drupal\Core\Cache\CacheTagInterface::tagCache', array());
    foreach ($this->flattenTags($tags) as $tag) {
      // Only delete tags once per request unless they are written again.
      if (isset($deleted_tags[$tag])) {
        continue;
      }
      $deleted_tags[$tag] = TRUE;
      unset($tag_cache[$tag]);
      try {
        $this->connection->merge('cache_tags')
          ->insertFields(array('deletions' => 1))
          ->expression('deletions', 'deletions + 1')
          ->key(array('tag' => $tag))
          ->execute();
      }
      catch (\Exception $e) {
        $this->catchException($e, 'cache_tags');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checksumTags(array $tags, $set_context) {
    $flat_tags = $this->flattenTags($tags);
    $tag_cache = &drupal_static('Drupal\Core\Cache\CacheTagInterface::tagCache', array());

    if ($set_context) {
      $deleted_tags = &drupal_static('Drupal\Core\Cache\DatabaseTag::deletedTags', array());
      // Remove tags that were already deleted during this request from the static
      // cache so that another deletion for them will be correctly updated.
      foreach ($flat_tags as $tag) {
        if (isset($deleted_tags[$tag])) {
          unset($deleted_tags[$tag]);
        }
      }
    }

    $checksum = array(
      'invalidations' => 0,
      'deletions' => 0,
    );

    $query_tags = array_diff($flat_tags, array_keys($tag_cache));
    if ($query_tags) {
      $db_tags = $this->connection->query('SELECT tag, invalidations, deletions FROM {cache_tags} WHERE tag IN (:tags)', array(':tags' => $query_tags))->fetchAllAssoc('tag', \PDO::FETCH_ASSOC);
      $tag_cache += $db_tags;

      // Fill static cache with empty objects for tags not found in the database.
      $tag_cache += array_fill_keys(array_diff($query_tags, array_keys($db_tags)), $checksum);
    }

    foreach ($flat_tags as $tag) {
      $checksum['invalidations'] += $tag_cache[$tag]['invalidations'];
      $checksum['deletions'] += $tag_cache[$tag]['deletions'];
    }

    return $checksum;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareGet(&$item) {
    $checksum = $this->checksumTags($item->tags, FALSE);

    // Check if deleteTags() has been called with any of the entry's tags.
    $item->deleted = $item->checksum_deletions != $checksum['deletions'];

    // Check if invalidateTags() has been called with any of the entry's tags.
    if ($item->checksum_invalidations != $checksum['invalidations']) {
      $item->valid = FALSE;
    }
  }

  /**
   * Act on an exception when cache might be stale.
   *
   * If the cache_tags table does not yet exist, that's fine but if the table
   * exists and yet the query failed, then the cache is stale and the
   * exception needs to propagate.
   *
   * @param $e
   *   The exception.
   * @param string|null $table_name
   *   The table name, defaults to $this->bin. Can be cache_tags.
   */
  protected function catchException(\Exception $e, $table_name = NULL) {
    if ($this->connection->schema()->tableExists($table_name ?: $this->bin)) {
      throw $e;
    }
  }

  /**
   * {@inheritdoc}.
   */
  public function clearCache() {
    // Nothing to do here.
  }
}
