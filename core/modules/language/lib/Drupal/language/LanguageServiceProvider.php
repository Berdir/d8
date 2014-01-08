<?php

/**
 * @file
 * Contains \Drupal\language\LanguageServiceProvider.
 */

namespace Drupal\language;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Language\Language;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the language_manager service to point to language's module one.
 */
class LanguageServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    // The following services are needed only on multilingual sites.
    if ($this->isMultilingual($container)) {
      $container->register('language_request_subscriber', 'Drupal\language\EventSubscriber\LanguageRequestSubscriber')
        ->addTag('event_subscriber')
        ->addArgument(new Reference('language_manager'))
        ->addArgument(new Reference('language_negotiator'))
        ->addArgument(new Reference('string_translation'))
        ->addArgument(new Reference('current_user'));

      $container->register('path_processor_language', 'Drupal\language\HttpKernel\PathProcessorLanguage')
        ->addTag('path_processor_inbound', array('priority' => 300))
        ->addTag('path_processor_outbound', array('priority' => 100))
        ->addArgument(new Reference('config.factory'))
        ->addArgument(new Reference('settings'))
        ->addArgument(new Reference('language_manager'))
        ->addArgument(new Reference('language_negotiator'))
        ->addArgument(new Reference('current_user'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('language_manager');
    $definition->setClass('Drupal\language\ConfigurableLanguageManager')
      ->addArgument(new Reference('config.factory'))
      ->addArgument(new Reference('module_handler'))
      ->addMethodCall('initConfigOverrides');
  }

  /**
   * Checks whether the site is multilingual.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container builder services are being registered to.
   *
   * @return bool
   *   TRUE if the site is multilingual, FALSE otherwise.
   */
  protected function isMultilingual(ContainerBuilder $container) {
    $prefix = 'language.entity.';
    $config_ids = array_filter($container->get('kernel.config.storage')->listAll($prefix), function($config_id) use ($prefix) {
      return $config_id != $prefix . Language::LANGCODE_NOT_SPECIFIED && $config_id != $prefix . Language::LANGCODE_NOT_APPLICABLE;
    });
    return count($config_ids) > 1;
  }

}
