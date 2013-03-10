<?php

/**
 * @file
 * Definition of Drupal\Core\EventSubscriber\ResponseSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Access subscriber for controller requests.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * Allows manipulation of the response object when performing a redirect.
   *
   * @param Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The Event to process.
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();

    if ($response instanceOf RedirectResponse) {
      $options = array();

      $request = $event->getRequest();
      $destination = $request->query->get('destination');

      // A destination in $_GET always overrides the function arguments.
      // We do not allow absolute URLs to be passed via $_GET, as this can be an
      // attack vector.
      if (!empty($destination)) {
        $destination = drupal_parse_url($destination);
      }
      else {
        $destination = drupal_parse_url($response->getTargetUrl());
      }

      $target_path = $destination['path'];
      $options['query'] = $destination['query'];
      $options['fragment'] = $destination['fragment'];

      // @todo: Use a custom redirect response for URL's with arguments?
      if (is_array($target_path)) {
        list($target_path, $options) = $target_path;
      }
      $options['absolute'] = TRUE;

      // Remove leading /.
      $target_path = ltrim($target_path, '/');

      // @todo this should be replaced by the event subscriber pattern?
      drupal_alter('redirect_response', $target_path, $options, $status);

      $response->setTargetUrl(url($target_path, $options));
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('onKernelResponse', 100);
    return $events;
  }
}
