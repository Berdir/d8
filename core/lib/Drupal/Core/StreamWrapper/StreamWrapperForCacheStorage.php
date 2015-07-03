<?php

/**
 * @file
 * Contains \Drupal\Core\StreamWrapper\StreamWrapperForCacheStorage.
 */

namespace Drupal\Core\StreamWrapper;
use Drupal\Core\PhpStorage\CacheStorage;

/**
 * The stream wrapper for \Drupal\Core\PhpStorage\CacheStorage .
 *
 * This class is not usable as a generic stream wrapper, it is specifically
 * written to work with \Drupal\Core\PhpStorage\CacheStorage and allows us
 * to manually set the mtime of the "file" the stream is wrapping.
 */
class StreamWrapperForCacheStorage {

  /**
   * @var \Drupal\Core\PhpStorage\CacheStorage
   */
  protected static $storage;

  /**
   * @var int
   */
  protected static $mtime;

  /**
   * Stream context resource.
   *
   * @var resource
   */
  public $context = NULL;

  /**
   * A generic resource handle.
   *
   * @var resource
   */
  protected static $handle = NULL;

  /**
   * Initialize the wrapper
   *
   * @param $storage
   *   The corresponding \Drupal\Core\PhpStorage\CacheStorage instance. This
   *   wil be used by stream_open().
   * @param $mtime
   *   The (fake) modified timestamp of the wrapped file.
   */
  public static function init(CacheStorage $storage, $mtime) {
    static::$storage = $storage;
    static::$mtime = $mtime;
  }

  public function stream_close () {
    return fclose(static::$handle);
  }

  public function stream_eof() {
    return feof(static::$handle);
  }

  public function stream_flush() {
    return fflush(static::$handle);
  }

  public function stream_open($path) {
    static::$handle = static::$storage->open($path);
    return (bool) static::$handle;
  }

  public function stream_read($count) {
    return fread(static::$handle, $count);
  }

  public function stream_stat() {
    return fstat(static::$handle);
  }

  public function url_stat() {
    $return = [
      'dev' => 0,
      'ino' => 0,
      'mode' => 0,
      'nlink' => 0,
      'uid' => 0,
      'gid' => 0,
      'rdev' => 0,
      'size' => 0,
      'atime' => 0,
      'mtime' => static::$mtime,
      'ctime' => 0,
      'blksize' => -1,
      'blocks' => -1,
    ];
    return $return + array_values($return);
  }

}
