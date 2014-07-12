<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\RouterRebuildSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Rebuilds the default menu links and runs menu-specific code if necessary.
 */
class RouterRebuildSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The menu link plugin manager
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager
   */
  protected $menuLinkManager;

  /**
   * Constructs the RouterRebuildSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route builder.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link plugin manager.
   */
  public function __construct(RouteBuilderInterface $route_builder, LockBackendInterface $lock, MenuLinkManagerInterface $menu_link_manager) {
    $this->routeBuilder = $route_builder;
    $this->lock = $lock;
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * Rebuilds routers if necessary.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The event object.
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    $this->routeBuilder->rebuildIfNeeded();
  }

  /**
   * Rebuilds the menu links and deletes the local_task cache tag.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event object.
   */
  public function onRouterRebuild(Event $event) {
    $this->menuLinksRebuild();
    Cache::deleteTags(array('local_task' => 1));
  }

  /**
   * Perform menu-specific rebuilding.
   */
  protected function menuLinksRebuild() {
    if ($this->lock->acquire(__FUNCTION__)) {
      $transaction = db_transaction();
      try {
        // Ensure the menu links are up to date.
        $this->menuLinkManager->rebuild();
        // Ignore any database replicas temporarily.
        db_ignore_replica();
      }
      catch (\Exception $e) {
        $transaction->rollback();
        watchdog_exception('menu', $e);
      }

      $this->lock->release(__FUNCTION__);
    }
    else {
      // Wait for another request that is already doing this work.
      // We choose to block here since otherwise the router item may not
      // be available during routing resulting in a 404.
      $this->lock->wait(__FUNCTION__);
    }
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
