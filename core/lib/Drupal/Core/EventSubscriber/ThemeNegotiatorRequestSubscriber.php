<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\ThemeNegotiatorRequestSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Initializes the theme for the current request.
 */
class ThemeNegotiatorRequestSubscriber implements EventSubscriberInterface {

  /**
   * The theme negotiator service.
   *
   * @var \Drupal\Core\Theme\ThemeNegotiatorInterface
   */
  protected $themeNegotiator;

  /**
   * Constructs a ThemeNegotiatorRequestSubscriber object.
   *
   * @param \Drupal\Core\Theme\ThemeNegotiatorInterface $theme_negotiator
   *   The theme negotiator service.
   */
  public function __construct(ThemeNegotiatorInterface $theme_negotiator) {
    $this->themeNegotiator = $theme_negotiator;
  }

  /**
   * Initializes the theme system after the routing system.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestThemeNegotiator(GetResponseEvent $event) {
    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
      if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update') {
        // @todo Refactor drupal_theme_initialize() into a request subscriber.
        // @see https://drupal.org/node/2228093
        drupal_theme_initialize($event->getRequest());
      }
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('onKernelRequestThemeNegotiator', 29);
    return $events;
  }

}
