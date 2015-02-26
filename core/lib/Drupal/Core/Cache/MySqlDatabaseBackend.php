<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\MySqlDatabaseBackend.
 */

namespace Drupal\Core\Cache;

/**
 * Mysql specific database backend implementation.
 *
 * Uses REPLACE queries for better performance on cache writes.
 *
 * @ingroup cache
 */
class MySqlDatabaseBackend extends DatabaseBackend {

  /**
   * {@inheritdoc}
   */
  protected function doSet($cid, $data, $expire, $tags) {
    $serialized = 0;
    if (!is_string($data)) {
      $data = serialize($data);
      $serialized = 1;
    }

    $this->connection->query("REPLACE {" . $this->bin . "} (cid, created, expire, tags, checksum, data, serialized) VALUES (:cid, :created, :expire, :tags, :checksum, :data, :serialized)", array(
      ':cid' => $this->normalizeCid($cid),
      ':created' => round(microtime(TRUE), 3),
      ':expire' => $expire,
      ':tags' => implode(' ', $tags),
      ':checksum' => $this->checksumProvider->getCurrentChecksum($tags),
      ':data' => $data,
      ':serialized' => $serialized
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function doSetMultiple(array $items) {
    $query_parts = array();
    $values = array();

    $index = 0;
    foreach ($items as $cid => $item) {
      $item += array(
        'expire' => CacheBackendInterface::CACHE_PERMANENT,
        'tags' => array(),
      );
      $index++;

      Cache::validateTags($item['tags']);
      $item['tags'] = array_unique($item['tags']);
      // Sort the cache tags so that they are stored consistently in the DB.
      sort($item['tags']);


      $serialized = 0;
      if (!is_string($item['data'])) {
        $item['data'] = serialize($item['data']);
        $serialized = 1;
      }
      $query_parts[] = "(:cid$index, :created$index, :expire$index, :tags$index, :checksum$index, :data$index, :serialized$index)";
      $values += array(
        ":cid$index" => $this->normalizeCid($cid),
        ":created$index" => round(microtime(TRUE), 3),
        ":expire$index" => $item['expire'],
        ":tags$index" => implode(' ', $item['tags']),
        ":checksum$index" => $this->checksumProvider->getCurrentChecksum($item['tags']),
        ":data$index" => $item['data'],
        ":serialized$index" => $serialized
      );
    }

    $query = "REPLACE {" . $this->bin . "} (cid, created, expire, tags, checksum, data, serialized) VALUES " . implode(', ', $query_parts);
    $this->connection->query($query, $values);
  }

}
