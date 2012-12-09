<?php

/**
 * @file
 * Contains Drupal\statistics\EventSubscriber\StatisticsCloseSubscriber.
 */

namespace Drupal\statistics\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for all responses.
 */
class StatisticsCloseSubscriber implements EventSubscriberInterface {

  /**
   * Performs end of request tasks.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The Event to process.
   */
  public function onTerminate(PostResponseEvent $event) {
    global $user;

    if (config('statistics.settings')->get('access_log.enabled')) {
      drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);

      // For anonymous users unicode.inc will not have been loaded.
      include_once DRUPAL_ROOT . '/core/includes/unicode.inc';
      // Log this page access.
      db_insert('accesslog')
        ->fields(array(
          'title' => truncate_utf8(strip_tags(drupal_get_title()), 255),
          // @todo The public function current_path() is not available on a cache
          //   hit.
          'path' => truncate_utf8(_current_path(), 255),
          'url' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
          'hostname' => ip_address(),
          'uid' => $user->uid,
          'sid' => session_id(),
          'timer' => (int) timer_read('page'),
          'timestamp' => REQUEST_TIME,
        ))
        ->execute();
    }
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
