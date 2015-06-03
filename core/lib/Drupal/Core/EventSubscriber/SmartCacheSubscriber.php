<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\SmartCacheSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\CacheContextsManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Uses the SmartCache as early as possible, to avoid as much work as possible.
 *
 * @see \Drupal\Core\Render\MainContent\HtmlRenderer
 */
class SmartCacheSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The cache contexts manager.
   *
   * @var \Drupal\Core\Cache\CacheContextsManager
   */
  protected $cacheContextsManager;

  /**
   * The Smart Cache contexts cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $smartContextsCache;

  /**
   * The Smart Cache #type => html render array cache bin.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $smartHtmlCache;

  /**
   * Constructs a new SmartCacheSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Cache\CacheContextsManager $cache_contexts_manager
   *   The cache contexts service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $contexts_cache
   *   The Smart Cache contexts cache bin.
   * @param \Drupal\Core\Cache\CacheBackendInterface $html_cache
   *   The Smart Cache #type => html render array cache bin.
   */
  public function __construct(RouteMatchInterface $route_match, CacheContextsManager $cache_contexts_manager, CacheBackendInterface $contexts_cache, CacheBackendInterface $html_cache) {
    $this->routeMatch = $route_match;
    $this->cacheContextsManager = $cache_contexts_manager;
    $this->smartContextsCache = $contexts_cache;
    $this->smartHtmlCache = $html_cache;
  }

  /**
   * Sets a response in case of a SmartCache cache hit.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event to process.
   */
  public function onRouteMatch(GetResponseEvent $event) {
    // SmartCache only supports master requests that are safe and ask for HTML.
    if (!$event->isMasterRequest() || !$event->getRequest()->isMethodSafe() || $event->getRequest()->getRequestFormat() !== 'html') {
      return;
    }

    // @todo For now, SmartCache doesn't handle admin routes. It may be too much
    //   work to add the necessary cacheability metadata to all admin routes
    //   before 8.0.0, but that can happen in 8.1.0 without a BC break.
    if ($this->routeMatch->getRouteObject()->getOption('_admin_route')) {
      return;
    }

    $this->routeMatch->getRouteName();

    // Get the contexts by which the current route's response must be varied.
    $cache_contexts = $this->smartContextsCache->get('smartcache:contexts:' . $this->cacheContextsManager->convertTokensToKeys(['route'])[0]);

    // If we already know the contexts by which the current route's response
    // must be varied, check if a response already is cached for the current
    // request's values for those contexts, and if so, return early.
    if ($cache_contexts !== FALSE) {
      $cid = 'smartcache:html_render_array:' . implode(':', $this->cacheContextsManager->convertTokensToKeys($cache_contexts->data));
      $cached_html = $this->smartHtmlCache->get($cid);
      if ($cached_html !== FALSE) {
        $html = $cached_html->data;
        $event->getRequest()
          ->attributes
          ->set('_controller', function() use ($html) {
            // Mark the render array, to skip as much in SmartCacheHtmlRenderer.
            $html['#smartcache'] = TRUE;
            // Return the #type => html render array. Let Symfony's HttpKernel
            // handle the conversion to a Response object via its VIEW event.
            return $html;
          });
        $event->stopPropagation();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRouteMatch', 27];

    return $events;
  }

}
