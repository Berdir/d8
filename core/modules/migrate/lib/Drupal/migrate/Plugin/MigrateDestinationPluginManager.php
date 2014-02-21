<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\MigrateDestinationPluginManager.
 */


namespace Drupal\migrate\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageManager;

class MigrateDestinationPluginManager extends MigratePluginManager {

  /**
   * The theme handler
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * An associative array where the keys are the enabled modules and themes.
   *
   * @var array
   */
  protected $providers;

  /**
   * {@inheritdoc}
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, EntityManagerInterface $entity_manager, $annotation = 'Drupal\migrate\Annotation\MigrateDestination') {
    parent::__construct($type, $namespaces, $cache_backend, $language_manager, $module_handler, $annotation);
    $this->themeHandler = $theme_handler;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    // By setting requirements_met to something in the annotation, this
    // handling can be skipped.
    if (!isset($definition['requirements_met'])) {
      if (substr($plugin_id, 0, 7) == 'entity:' && !$this->entityManager->getDefinition(substr($plugin_id, 7))) {
        $definition['requirements_met'] = FALSE;
      }
      elseif (isset($definition['destination_provider'])) {
        if (!isset($this->providers)) {
          $this->providers = $this->moduleHandler->getModuleList();
          foreach ($this->themeHandler->listInfo() as $theme => $info) {
            if ($info->status) {
              $this->providers[$theme] = TRUE;
            }
          }
        }
        $definition['requirements_met'] = isset($this->providers[$definition['destination_provider']]);
      }
    }
  }

}
