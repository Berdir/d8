<?php

/**
 * @file
 *
 * Contains \Drupal\field\Plugin\Type\FieldType\FieldTypePluginManager.
 */

namespace Drupal\field\Plugin\Type\FieldType;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\field\Plugin\Type\FieldType\LegacyFieldTypeDiscoveryDecorator;
use Drupal\Component\Plugin\Factory\ReflectionFactory;

/**
 * 'Configurable field type' plugin manager.
 *
 * @todo This is currently only used for discovery, the plugin classes are never
 * instanciated through this manager as 'Configurable field type' plugins.
 * Instead, field_data_type_info() adds them as 'data type' plugins through the
 * Drupal\field\Plugin\DataType\CFieldDataTypeDerivative derivative, and they
 * only get instanciated as such.
 * This is a conceptual mess, needs to be sorted out.
 */
class FieldTypePluginManager extends PluginManagerBase {

  /**
   * {@inheritdoc}
   */
  protected $defaults = array(
    'settings' => array(),
    'instance_settings' => array(),
    'list_class' => '\Drupal\field\Plugin\Type\FieldType\CField',
  );

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, ModuleHandlerInterface $module_handler) {
    $this->discovery = new AnnotatedClassDiscovery('field/field_type', $namespaces);
    // @todo Remove once all core field types have been converted (see
    // http://drupal.org/node/2014671).
    $this->discovery = new LegacyFieldTypeDiscoveryDecorator($this->discovery, $module_handler);
    $this->discovery = new ProcessDecorator($this->discovery, array($this, 'processDefinition'));
    $this->discovery = new AlterDecorator($this->discovery, 'field_info');
    $this->discovery = new CacheDecorator($this->discovery, 'field_types',  'field');

    $this->factory = new ReflectionFactory($this);
  }

}
