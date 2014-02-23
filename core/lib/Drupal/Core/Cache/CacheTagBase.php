<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\CacheTagBase.
 */

namespace Drupal\Core\Cache;

/**
 * Class CacheTagBase
 */
abstract class CacheTagBase implements CacheTagInterface {

  /**
   * 'Flattens' a tags array into an array of strings.
   *
   * @param array $tags
   *   Associative array of tags to flatten.
   *
   * @return array
   *   An indexed array of flattened tag identifiers.
   */
  public function flattenTags(array $tags) {
    if (isset($tags[0])) {
      return $tags;
    }

    $flat_tags = array();
    foreach ($tags as $namespace => $values) {
      if (is_array($values)) {
        foreach ($values as $value) {
          $flat_tags[] = "$namespace:$value";
        }
      }
      else {
        $flat_tags[] = "$namespace:$values";
      }
    }
    return $flat_tags;
  }

}
