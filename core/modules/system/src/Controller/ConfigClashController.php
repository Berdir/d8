<?php

/**
 * @file
 * Contains \Drupal\system\Form\ConfigClashController.
 */

namespace Drupal\system\Controller;

use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Builds a confirmation form for enabling modules with dependencies.
 */
class ConfigClashController extends ControllerBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The expirable key value store.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface
   */
  protected $keyValueExpirable;

  /**
   * The configuration installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * An associative list of modules to enable or disable.
   *
   * @var array
   */
  protected $modules = array();

  /**
   * Constructs a ConfigClashController object.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface $key_value_expirable
   *   The key value expirable factory.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   */
  public function __construct(KeyValueStoreExpirableInterface $key_value_expirable, ConfigManagerInterface $config_manager, ConfigInstallerInterface $config_installer) {
    $this->keyValueExpirable = $key_value_expirable;
    $this->configManager = $config_manager;
    $this->configInstaller = $config_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('keyvalue.expirable')->get('module_list'),
      $container->get('config.manager'),
      $container->get('config.installer')
    );
  }

  /**
   * Creates a report for modules.
   *
   * @see \Drupal\system\Form\ModulesListForm::submitForm()
   *
   * @return array
   *   The report as a render array.
   */
  public function moduleReport() {
    $account = $this->currentUser()->id();
    $modules = $this->keyValueExpirable->get($account);

    // hack to test
    // $modules = array('install' => array('config_install_fail_test' => TRUE));

    // Redirect to the modules list page if the key value store is empty.
    if (!$modules) {
      return new RedirectResponse($this->urlGenerator()->generate('system.modules_list', array(), TRUE));
    }

    $report = array(
      'description' => array(
        '#prefix' => '<p>',
        '#suffix' => '</p>',
        '#markup' => $this->t('You must delete or rename the following configuration to install @module.', array('@module' => implode(', ', $modules['install'])))
      ),
    ) + $this->report('module', array_keys($modules['install']));

    return $report;
  }

  /**
   * Generates a report on pre-existing configuration for a list of extensions.
   *
   * @param string $type
   *   The type of extension being installed. Either 'theme' or 'module'.
   * @param array $extensions
   *   The list of extensions that are being installed.
   *
   * @return array
   *   The report as a render array.
   */
  protected function report($type, array $extensions) {
    // Check if we have any pre existing configuration.
    $existing_configuration = array();
    foreach ($extensions as $extension) {
      $existing_configuration = array_merge_recursive($this->configInstaller->findPreExistingConfiguration($type, $extension), $existing_configuration);
    }

    $report['config_clashes'] = array(
      '#theme' => 'item_list',
      '#items' => array(),
      '#empty' => $this->t('No configuration clashes detected.'),
      '#weight' => 10,
    );

    if (count($existing_configuration)) {
      foreach ($existing_configuration as $collection => $config_names) {
        foreach ($config_names as $config_name) {
          $entity = $this->configManager->loadConfigEntityByName($config_name);
          if ($entity) {
            $report['config_clashes']['#items'][] = array(
              '#type' => 'link',
              '#title' => $entity->getEntityType()->getLabel() . ': '. $entity->label(),
            ) + $entity->urlInfo('edit-form')->toRenderArray();
          }
          else {
            // Super weird.
          }
        }
      }
    }

    return $report;
  }

}
