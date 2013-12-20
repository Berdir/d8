<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityTypes
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Discovery\YamlDiscovery;
use Drupal\Core\AnnotationReader;

/**
 * Defines an object which generates entity type domain objects.
 */
class EntityTypes {

  /**
   * @var \Drupal\Core\Entity\EntityType[]
   */
  protected $types = array();

  /**
   * @var bool
   */
  protected $preloaded = FALSE;

  /**
   * @var array
   */
  protected $class_mapping = array();

  /**
   * @var \Drupal\Core\Discovery\YamlDiscovery
   */
  protected $file_finder;

  /**
   * Returns all entity types defined in yml files.
   *
   * @return \Drupal\Core\Entity\Annotation\EntityType[]
   */
  public function findAll() {
    if (!$this->preloaded) {
      $class_mappings = $this->loadMappingsFromFiles();
      foreach ($class_mappings as $name => $class) {
        $this->types[$name] = $this->factory($class);
      }
      $this->invokeHooks();
      $this->preloaded = TRUE;
    }
    return $this->types;
  }

  /**
   * @param string $name
   *
   * @return \Drupal\Core\Entity\EntityType
   * @throws \RuntimeException
   */
  public function findByName($name) {
    if (isset($this->types[$name])) {
      return $this->types[$name];
    }

    if ($class = $this->getClassFromEntityName($name)) {
      $this->types[$name] = $this->factory($class);
      $this->invokeHooks();
      return $this->types[$name];
    }
    else {
      throw new \RuntimeException(sprintf("No such entity type %s!", $name));
    }
  }

  /**
   * @param string $class
   *
   * @return \Drupal\Core\Entity\EntityType
   */
  protected function factory($class) {
    $values = $this->getAnnotation($class) + array('class' => $class);
    return new EntityType($values);
  }

  /**
   */
  protected function invokeHooks() {
    foreach ($this->moduleHandler()->getImplementations('entity_info') as $module) {
      $function = $module . '_entity_info';
      $function($this->types);
    }
    $this->moduleHandler()->alter('entity_info', $this->types);
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  protected function getClassFromEntityName($name) {
    $class_mappings = $this->loadMappingsFromFiles();
    return isset($class_mappings[$name]) ? $class_mappings[$name] : FALSE;
  }

  /**
   * @return array
   */
  protected function loadMappingsFromFiles() {
    if (empty($this->class_mapping)) {
      $all = $this->fileFinder()->findAll();
      $this->class_mapping = call_user_func_array("array_merge", $all);
    }
    return $this->class_mapping;
  }

  /**
   * @return string
   */
  protected function name() {
    return 'entity';
  }

  /**
   * @param string $classname
   *
   * @return mixed
   */
  protected function getAnnotation($classname) {
    $entity_type_annotation = $this->getAnnotationReader()
      ->getClassAnnotation($classname, 'Drupal\Core\Entity\Annotation\EntityType');
    return $entity_type_annotation->get();
  }

  /**
   * @return \Drupal\Core\Discovery\YamlDiscovery
   */
  protected function fileFinder() {
    if (!isset($this->file_finder)) {
      $this->file_finder = new YamlDiscovery($this->name(), $this->directories());
    }
    return $this->file_finder;
  }

  /**
   * @return array
   */
  protected function directories() {
    return $this->moduleHandler()->getModuleDirectories();
  }

  /**
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected function moduleHandler() {
    return \Drupal::moduleHandler();
  }

  /**
   * @return AnnotationReader
   */
  protected function getAnnotationReader() {
    if (!isset($this->annotation_reader)) {
      $this->annotation_reader = new AnnotationReader();
    }
    return $this->annotation_reader;
  }

}
