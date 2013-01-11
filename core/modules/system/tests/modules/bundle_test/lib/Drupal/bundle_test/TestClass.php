<?php

/**
 * @file
 * Definition of Drupal\bundle_test\TestClass.
 */

namespace Drupal\bundle_test;

use Drupal\Core\KeyValueStore\KeyValueFactory;
use Drupal\Core\TerminationInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TestClass implements EventSubscriberInterface, TerminationInterface {

  /**
   * The state keyvalue collection.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  protected $state;

  public function __construct(KeyValueFactory $keyvalueFactory) {
    $this->state = $keyvalueFactory->get('state');
  }

  /**
   * A simple kernel listener method.
   */
  public function onKernelRequestTest(GetResponseEvent $event) {
    drupal_set_message(t('The bundle_test event subscriber fired!'));
  }

  /**
   * Registers methods as kernel listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('onKernelRequestTest', 100);
    return $events;
  }

  /**
   * Implements \Drupal\Core\TerminationInterface::terminate().
   */
  public function terminate() {
    $this->state->set('bundle_test.terminated', TRUE);
  }
}
