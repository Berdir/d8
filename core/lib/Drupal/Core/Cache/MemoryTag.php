<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\DatabaseTag.
 */

namespace Drupal\Core\Cache;

/**
 * Class DatabaseTag.
 */
class MemoryTag extends CacheTagBase {
  /**
   * The cache tag storage.
   *
   * @var array
   */
  protected $storage = array();

  /**
   * Ensure there is an entry in storage array for a given tag.
   *
   * @param $tag string
   *   Tag name.
   */
  protected function ensureItem($tag) {
    if (empty($this->storage[$tag])) {
      $this->storage[$tag] = array(
        'invalidations' => 0,
        'deletions' => 0,
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    foreach ($this->flattenTags($tags) as $tag) {
      $this->ensureItem($tag);
      $this->storage[$tag]['invalidations']++;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTags(array $tags) {
    foreach ($this->flattenTags($tags) as $tag) {
      $this->ensureItem($tag);
      $this->storage[$tag]['deletions']++;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function checksumTags(array $tags, $set_context) {
    $flat_tags = $this->flattenTags($tags);
    $checksum = array(
      'invalidations' => 0,
      'deletions' => 0,
    );

    foreach ($flat_tags as $tag) {
      $this->ensureItem($tag);
      $checksum['invalidations'] += $this->storage[$tag]['invalidations'];
      $checksum['deletions'] += $this->storage[$tag]['deletions'];
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
   * {@inheritdoc}.
   */
  public function clearCache() {
    // Nothing to be done here.
  }
}
