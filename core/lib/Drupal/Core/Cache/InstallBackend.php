<?php

/**
 * @file
 * Definition of Drupal\Core\Cache\InstallBackend.
 */

namespace Drupal\Core\Cache;

use Exception;

/**
 * Defines a stub cache implementation to be used during installation.
 *
 * The stub implementation is needed when database access is not yet available.
 * Because Drupal's caching system never requires that cached data be present,
 * these stub functions can short-circuit the process and sidestep the need for
 * any persistent storage. Obviously, using this cache implementation during
 * normal operations would have a negative impact on performance.
 *
 * If there is a database cache, this backend will attempt to clear it whenever
 * possible. The reason for doing this is that the database cache can accumulate
 * data during installation due to any full bootstraps that may occur at the
 * same time (for example, Ajax requests triggered by the installer). If we
 * didn't try to clear it whenever one of the delete function are called, the
 * data in the cache would become stale; for example, the installer sometimes
 * calls variable_set(), which updates the {variable} table and then clears the
 * cache to make sure that the next page request picks up the new value.
 * Not actually clearing the cache here therefore leads old variables to be
 * loaded on the first page requests after installation, which can cause
 * subtle bugs, some of which would not be fixed unless the site
 * administrator cleared the cache manually.
 */
class InstallBackend extends DatabaseBackend {

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::get().
   */
  function get($cid) {
    return FALSE;
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::getMultiple().
   */
  function getMultiple(&$cids) {
    return array();
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::set().
   */
  function set($cid, $data, $expire = CacheBackendInterface::CACHE_PERMANENT, array $tags = array()) {}

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::delete().
   */
  function delete($cid) {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::delete($cid);
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::deleteMultiple().
   */
  function deleteMultiple(array $cids) {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::deleteMultiple($cids);
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::deletePrefix().
   */
  function deletePrefix($prefix) {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::deletePrefix($prefix);
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::invalidateTags().
   */
  function invalidateTags(array $tags) {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::invalidateTags($tags);
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::flush().
   */
  function flush() {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::flush();
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::expire().
   */
  function expire() {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::expire();
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::garbageCollection().
   */
  function garbageCollection() {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        parent::garbageCollection();
      }
    }
    catch (Exception $e) {}
  }

  /**
   * Overrides Drupal\Core\Cache\DatabaseBackend::isEmpty().
   */
  function isEmpty() {
    try {
      if (class_exists('Drupal\Core\Database\Database')) {
        return parent::isEmpty();
      }
    }
    catch (Exception $e) {}
    return TRUE;
  }
}
