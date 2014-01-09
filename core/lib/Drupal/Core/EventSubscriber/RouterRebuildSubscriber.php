<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\RouterRebuildSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Rebuilds the router and menu_router if necessary.
 */
class RouterRebuildSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route builder.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   */
  public function __construct(RouteBuilderInterface $route_builder, CacheBackendInterface $cache_backend) {
    $this->routeBuilder = $route_builder;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * Rebuilds routers if necessary.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event object.
   */
  public function onKernelTerminate(Event $event) {
    $this->routeBuilder->rebuildIfNeeded();
  }

  public function onRouterRebuild(Event $event) {
    menu_router_rebuild();
    $this->cacheBackend->deleteTags(array('local_task' => 1));
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::TERMINATE][] = array('onKernelTerminate', 200);
    $events[RoutingEvents::FINISHED][] = array('onRouterRebuild', 200);
    return $events;
  }

}
