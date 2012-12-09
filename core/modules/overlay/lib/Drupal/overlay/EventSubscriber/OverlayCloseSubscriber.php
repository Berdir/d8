<?php

/**
 * @file
 * Contains Drupal\overlay\EventSubscriber\OverlayCloseSubscriber.
 */

namespace Drupal\overlay\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for all responses.
 */
class OverlayCloseSubscriber implements EventSubscriberInterface {

  /**
   * Performs end of request tasks.
   *
   * When viewing an overlay child page, check if we need to trigger a refresh of
   * the supplemental regions of the overlay on the next page request.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The Event to process.
   */
  public function onTerminate(PostResponseEvent $event) {
    // Check that we are in an overlay child page. Note that this should never
    // return TRUE on a cached page view, since the child mode is not set until
    // overlay_init() is called.
    if (overlay_get_mode() == 'child') {
      // Load any markup that was stored earlier in the page request, via calls
      // to overlay_store_rendered_content(). If none was stored, this is not a
      // page request where we expect any changes to the overlay supplemental
      // regions to have occurred, so we do not need to proceed any further.
      $original_markup = overlay_get_rendered_content();
      if (!empty($original_markup)) {
        // Compare the original markup to the current markup that we get from
        // rendering each overlay supplemental region now. If they don't match,
        // something must have changed, so we request a refresh of that region
        // within the parent window on the next page request.
        foreach (overlay_supplemental_regions() as $region) {
          if (!isset($original_markup[$region]) || $original_markup[$region] != overlay_render_region($region)) {
            overlay_request_refresh($region);
          }
        }
      }
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
