<?php
/**
 * @file
 * Contains \Drupal\Core\Config\ConfigCachePrimeSubscriber.
 */

namespace Drupal\Core\Config;

use Drupal\Component\Utility\Settings;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Core configuration cache prime subscriber.
 */
class ConfigCachePrimeSubscriber implements EventSubscriberInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ConfigCachePrimeSubscriber object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Preloads the configuration objects in Settings.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The route finish event.
   */
  public function onRequestPrimeConfigCache(Event $event) {
    $settings_config_names = Settings::getInstance()->get('config_cache_prime_names', array());
    var_dump($settings_config_names);
    if ($settings_config_names) {
      $this->configFactory->loadMultiple($settings_config_names);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Preload configuration after the language negotiation happened, when
    // enabled.
    $events[KernelEvents::REQUEST][] = array('onRequestPrimeConfigCache', 250);
    return $events;
  }

}
