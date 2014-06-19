<?php

/**
 * @file
 * Contains \Drupal\Core\Routing\CachedUrlGenerator.
 */

namespace Drupal\Core\Routing;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\DestructableInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\Language;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;

/**
 * Class used to wrap a UrlGenerator to provide caching of the generated values.
 */
class CachedUrlGenerator implements DestructableInterface, CachedUrlGeneratorInterface {

  /**
   * The wrapped URL generator
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Language manager for retrieving the URL language type.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * An array of cached URLs keyed by route name or path.
   *
   * @var array
   */
  protected $cachedUrls = array();

  /**
   * Whether the cache needs to be written.
   *
   * @var boolean
   */
  protected $cacheNeedsWriting = FALSE;

  /**
   * The cache key to use when caching generated URLs.
   *
   * @var string
   */
  protected $cacheKey;

  /**
   * Cache prefix for route names.
   */
  const ROUTE_CACHE_PREFIX = 'route::';

  /**
   * Cache prefix for paths.
   */
  const PATH_CACHE_PREFIX = 'path::';

  /**
   * Constructs a \Drupal\Core\Routing\CachedUrlGenerator.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The wrapped URL generator
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(UrlGeneratorInterface $url_generator, CacheBackendInterface $cache, LanguageManagerInterface $language_manager) {
    $this->urlGenerator = $url_generator;
    $this->cache = $cache;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function clearCache() {
    $this->cachedUrls = array();
    $this->cache->delete($this->cacheKey);
  }

  /**
   * Writes the cache of generated URLs.
   */
  protected function writeCache() {
    if ($this->cacheNeedsWriting && !empty($this->cachedUrls) && !empty($this->cacheKey)) {
      // Set the URL cache to expire in 24 hours.
      $expire = REQUEST_TIME + (60 * 60 * 24);
      $this->cache->set($this->cacheKey, $this->cachedUrls, $expire);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function generate($name, $parameters = array(), $absolute = FALSE) {
    $options = array();
    // We essentially inline the implentation from the Drupal UrlGenerator
    // and avoid setting $options so that we increase the liklihood of caching.
    if ($absolute) {
      $options['absolute'] = $absolute;
    }
    return $this->generateFromRoute($name, $parameters, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function generateFromPath($path = NULL, $options = array()) {
    $key = self::PATH_CACHE_PREFIX . hash('sha256', $path . serialize($options));
    if (!isset($this->cachedUrls[$key])) {
      $this->cachedUrls[$key] = $this->urlGenerator->generateFromPath($path, $options);
      $this->cacheNeedsWriting = TRUE;
    }
    return $this->cachedUrls[$key];
  }

  /**
   * {@inheritdoc}
   */
  public function generateFromRoute($name, $parameters = array(), $options = array()) {
    // In some cases $name may be a Route object, rather than a string.
    $key = self::ROUTE_CACHE_PREFIX . hash('sha256', serialize($name) . serialize($options) . serialize($parameters));
    if (!isset($this->cachedUrls[$key])) {
      $this->cachedUrls[$key] = $this->urlGenerator->generateFromRoute($name, $parameters, $options);
      $this->cacheNeedsWriting = TRUE;
    }
    return $this->cachedUrls[$key];
  }

  /**
   * {@inheritdoc}
   */
  public function setRequest(Request $request) {
    $this->cacheKey = $request->attributes->get('_system_path');
    // Only multilingual sites have language dependant URLs.
    if ($this->languageManager->isMultilingual()) {
      $this->cacheKey .= '::' . $this->languageManager->getCurrentLanguage(Language::TYPE_URL)->id;
    }
    $cached = $this->cache->get($this->cacheKey);
    if ($cached) {
      $this->cachedUrls = $cached->data;
    }
    $this->urlGenerator->setRequest($request);
  }

  /**
   * {@inheritdoc}
   */
  public function setBaseUrl($url) {
    $this->urlGenerator->setBaseUrl($url);
  }

  /**
   * {@inheritdoc}
   */
  public function setBasePath($path) {
    $this->urlGenerator->setBasePath($path);
  }

  /**
   * {@inheritdoc}
   */
  public function setScriptPath($path) {
    $this->urlGenerator->setScriptPath($path);
  }

  /**
   * {@inheritdoc}
   */
  public function supports($name) {
    return $this->urlGenerator->supports($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteDebugMessage($name, array $parameters = array()) {
    return $this->urlGenerator->getRouteDebugMessage($name, $parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    $this->writeCache();
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(RequestContext $context) {
    $this->urlGenerator->setContext($context);
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    return $this->urlGenerator->getContext();
  }

  /**
   * {@inheritdoc}
   */
  public function getPathFromRoute($name, $parameters = array()) {
    return $this->urlGenerator->getPathFromRoute($name, $parameters);
  }


}
