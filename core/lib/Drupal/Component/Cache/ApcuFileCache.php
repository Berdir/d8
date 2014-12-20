<?php

/**
 * @file
 * Contains \Drupal\Core\Cache\ApcuFileCAche.
 */

namespace Drupal\Component\Cache;

/**
 * Allows to cache data based on file modification dates.
 */
class ApcuFileCache {

  /**
   * Prefix that is used for apcu cache entries.
   *
   * The prefix is static, as all file paths are stored with the full path, so
   * a per-site prefix is not needed.
   */
  const APC_PREFIX = 'apcu_file_cache:';

  /**
   * Static cache that contains already loaded cache entries.
   *
   * @var array
   */
  protected static $cached = [];

  /**
   * Gets cached data based on a filename.
   *
   * @param string $filepath
   *   Name of the cache that the cached data is based on.
   *
   * @return mixed|null
   *   The data that was persisted with set() or NULL if there is no data
   *   or the file has been modified.
   */
  public static function get($filepath) {
    // Do nothing if apcu is not available.
    if (!function_exists('apc_fetch')) {
      return NULL;
    }

    // Ensure that we cache the full path of the file.
    $filepath = realpath($filepath);

    // Load the data from the static cache or apcu.
    if (isset(static::$cached[$filepath])) {
      $cached = static::$cached[$filepath];
    }
    else {
      if (!file_exists($filepath)) {
        return NULL;
      }
      $cached = apc_fetch(static::APC_PREFIX . $filepath);
      if ($cached) {
        static::$cached[$filepath] = $cached;
      }
    }

    if (!empty($cached)) {
      // If there is data, compare the file modification time, only return it
      // if there is a match.
      $modification_time = filemtime($filepath);
      if ($cached['mtime'] == $modification_time) {
        return $cached['data'];
      }
    }

  }

  /**
   * @param $filepath
   * @param $data
   *
   * @return null
   */
  public static function set($filepath, $data) {
    if (!function_exists('apc_store')) {
      return NULL;
    }
    $filepath = realpath($filepath);
    $cached = [
      'mtime' => filemtime($filepath),
      'data' => $data,
    ];

    apc_store(static::APC_PREFIX . $filepath, $cached);
    static::$cached[$filepath] = $cached;

  }

  public static function delete($filepath) {
    if (!function_exists('apc_delete')) {
      return NULL;
    }
    $filepath = realpath($filepath);
    debug($filepath);
    apc_delete(static::APC_PREFIX . $filepath);
    unset(static::$cached[$filepath]);
  }

}
