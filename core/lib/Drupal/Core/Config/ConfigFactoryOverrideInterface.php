<?php

/**
 * @file
 * Contains \Drupal\Core\Config\ConfigFactoryOverrideInterface.
 */

namespace Drupal\Core\Config;

/**
 * Defines the interface for a configuration factory override object.
 */
interface ConfigFactoryOverrideInterface {

  /**
   * Returns config overrides.
   *
   * @param array $names
   *   A list of configuration names that are being loaded.
   *
   * @return array
   *   An array keyed by configuration name of override data. Override data
   *   contains a nested array structure of overrides.
   */
  public function loadOverrides($names);

  /**
   * The string to append to the configuration static cache name.
   *
   * @return string
   *   A string to append to the configuration static cache name.
   */
  public function getCacheSuffix();

  /**
   * Creates a configuration object for use during install and synchronization.
   *
   * If the overrider stores its overrides in configuration collections then
   * it can have its own implementation of
   * \Drupal\Core\Config\StorableConfigBase. Configuration overriders can link
   * themselves to a configuration collection by listening to the
   * \Drupal\Core\Config\ConfigEvents::COLLECTION_INFO event and adding the
   * collections they are responsible for. Doing this will allow installation
   * and synchronization to use the overrider's implementation of
   * StorableConfigBase.
   *
   * @see \Drupal\Core\Config\ConfigCollectionInfo
   * @see \Drupal\Core\Config\ConfigImporter::importConfig()
   * @see \Drupal\Core\Config\ConfigInstaller::createConfiguration()
   *
   * @param string $name
   *   The configuration object name.
   * @param string $collection
   *   The configuration collection.
   *
   * @return \Drupal\Core\Config\StorableConfigBase
   *   The configuration object for the provided name and collection.
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION);

  /**
   * The cache contexts associated with this config factory override.
   *
   * These identify a specific variation/representation of the object.
   *
   * Cache contexts are tokens: placeholders that are converted to cache keys by
   * the @cache_contexts_manager service. The replacement value depends on the
   * request context (the current URL, language, and so on). They're converted
   * before storing or retrieving an object in cache.
   *
   * @param string $name
   *   The name of the configuration object that is being constructed.
   *
   * @return string[]
   *   An array of cache context tokens, used to generate a cache ID.
   *
   * @see \Drupal\Core\Cache\Context\CacheContextsManager::convertTokensToKeys()
   */
  public function getCacheContexts($name);

  /**
   * The cache tags associated with this config factory override.
   *
   * When this object is modified, these cache tags will be invalidated.
   *
   * Since a cache tag is already associated with every config object this
   * should only be used if multiple config objects are being overridden.
   *
   * @param string $name
   *   The name of the configuration object that is being constructed.
   *
   * @return string[]
   *  A set of cache tags.
   */
  public function getCacheTags($name);

  /**
   * The maximum age for which this object may be cached.
   *
   * @param string $name
   *   The name of the configuration object that is being constructed.
   *
   * @return int
   *   The maximum time in seconds that this object may be cached.
   */
  public function getCacheMaxAge($name);

}
