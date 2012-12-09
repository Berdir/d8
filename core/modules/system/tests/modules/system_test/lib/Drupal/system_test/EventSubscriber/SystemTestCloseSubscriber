<?php

/**
 * @file
 * Contains Drupal\system_test\EventSubscriber\SystemTestCloseSubscriber.
 */

namespace Drupal\system_test\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for all responses.
 */
class SystemTestCloseSubscriber implements EventSubscriberInterface {

  /**
   * Performs end of request tasks.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The Event to process.
   */
  public function onTerminate(PostResponseEvent $event) {
    watchdog('system_test', 'SystemTestCloseSubscriber');
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::TERMINATE][] = array('onTerminate', 50);

    return $events;
  }
}
