<?php

/**
 * @file
 * Contains \Drupal\Core\EventSubscriber\KernelTerminateSubscriber.
 */

namespace Drupal\Core\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * This terminates iniated services tagged with needs_termination.
 */
class KernelTerminateSubscriber extends ContainerAware implements EventSubscriberInterface {

  /**
   * Holds an array of service ID's that will require termination.
   *
   * @var array
   */
  protected $services;

  /**
   * Registers a a service for termination.
   *
   * Calls to this method are set up in
   * RegisterServicesForTerminationPass::terminate().
   *
   * @param string $id
   *   Name of the service.
   */
  public function registerService($id) {
    $this->services[] = $id;
  }

  /**
   * Invoked by the terminate kernel event.
   *
   * @param \Symfony\Component\HttpKernel\Event\PostResponseEvent $event
   *   The event object.
   */
  public function onKernelTerminate(PostResponseEvent $event) {
    $this->terminateInitiatedServices();
  }

  /**
   * Terminates registered services if necessary.
   */
  protected function terminateInitiatedServices() {
    foreach ($this->services as $id) {
      // Check if the service was initialized during this request, termination
      // is not necessary if the service was not used.
      if ($this->container->initialized($id)) {
        $service = $this->container->get($id);
        $service->terminate();
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
    $events[KernelEvents::TERMINATE][] = array('onKernelTerminate', 100);
    return $events;
  }
}
