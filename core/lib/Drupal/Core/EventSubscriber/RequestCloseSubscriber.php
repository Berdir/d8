<?php

/**
 * @file
 * Definition of Drupal\Core\EventSubscriber\RequestCloseSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\ExtensionHandlerInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for all responses.
 */
class RequestCloseSubscriber implements EventSubscriberInterface {

  /**
   * @var ExtensionHandlerInterface
   */
  protected $extensionHandler;

  /**
   * Constructor.
   */
  function __construct(ExtensionHandlerInterface $extension_handler) {
    $this->extensionHandler = $extension_handler;
  }

  /**
   * Performs end of request tasks.
   *
   * @todo The body of this function has just been copied almost verbatim from
   *   drupal_page_footer(). There's probably a lot in here that needs to get
   *   removed/changed. Also, if possible, do more light-weight shutdowns on
   *   AJAX requests.
   *
   * @param Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The Event to process.
   */
  public function onTerminate(PostResponseEvent $event) {
    module_invoke_all('exit');
    drupal_cache_system_paths();
    $request_method = $event->getRequest()->getMethod();
    // Check whether we need to write the module implementations cache. We do
    // not want to cache hooks which are only invoked on HTTP POST requests
    // since these do not need to be optimized as tightly, and not doing so
    // keeps the cache entry smaller.
    if ($request_method == 'GET' || $request_method == 'HEAD') {
      $this->extensionHandler->writeModuleImplementationsCache();
    }
    system_run_automated_cron();
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::TERMINATE][] = array('onTerminate');

    return $events;
  }
}
