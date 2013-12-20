<?php

/**
 * @file
 * Contains \Drupal\Core\AnnotationReader.
 */

namespace Drupal\Core;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Reflection\StaticReflectionParser;

/**
 */
class AnnotationReader {

  /**
   * @var bool
   */
  protected $initialized;

  /**
   * @var \Doctrine\Common\Annotations\AnnotationReader
   */
  protected $reader;

  /**
   * @var object
   */
  protected $loader;

  /**
   * @param string $class_name
   * @param string $annotation_name
   *
   * @return mixed
   */
  public function getClassAnnotation($class_name, $annotation_name) {
    $annotations = $this->getClassAnnotations($class_name);
    $filtered_annotations = array();
    foreach ($annotations as $annotation) {
      if ($annotation instanceOf $annotation_name) {
        return $annotation;
      }
    }
  }

  /**
   * @param string $class_name
   *
   * @return array
   */
  public function getClassAnnotations($class_name) {
    return $this->getFromCache($class_name) ?: $this->doGetClassAnnotations($class_name);
  }

  /**
   * @param string $class_name
   *
   * @return array
   */
  protected function doGetClassAnnotations($class_name) {
    $this->initialize();

    $annotations = array();
    if (class_exists($class_name)) {
      $reflection_class = new \ReflectionClass($class_name);
      $annotations = $this->reader()->getClassAnnotations($reflection_class);
      $this->setCache($class_name, $annotations);
    }
    return $annotations;
  }

  /**
   * @param string $class_name
   *
   * @return array
   */
  protected function doGetClassAnnotationsStatic($class_name) {
    $this->initialize();
    $parser = new StaticReflectionParser($class_name, $this->getLoader());
    $reflection_class = $parser->getReflectionClass();
    $annotations = $this->reader()->getClassAnnotations($reflection_class);
    $this->setCache($class_name, $annotations);
    return $annotations;
  }

  /**
   * @return DoctrineAnnotationReader
   */
  protected function reader() {
    if (!isset($this->reader)) {
      $this->reader = new DoctrineAnnotationReader();
      foreach ($this->globalIgnoreNames() as $name) {
        $this->reader->addGlobalIgnoredName($name);
      }
    }
    return $this->reader;
  }

  /**
   * @param $reader
   */
  public function setReader($reader) {
    $this->reader = $reader;
  }

  /**
   * @return array
   */
  protected function globalIgnoreNames() {
    return array('endlink');
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  protected function getFromCache($name) {
    return isset($this->cache[$name]) ? $this->cache[$name] : FALSE;
  }

  /**
   * @param string $name
   * @param mixed $value
   */
  protected function setCache($name, $value) {
    $this->cache[$name] = $value;
  }

  /**
   * @return \Symfony\Component\ClassLoader\ClassLoader
   */
  protected function getLoader() {
    return $this->loader ?: $this->defaultLoader();
  }

  /**
   * @param $loader
   */
  public function setLoader($loader) {
    $this->loader = $loader;
  }

  /**
   * @return \Symfony\Component\ClassLoader\ClassLoader
   */
  protected function defaultLoader() {
    return drupal_classloader();
  }

  /**
   */
  protected function initialize() {
    if (!$this->initialized) {
      // If the default loader isn't already callable, it's probably an instance
      // of the class loader, so make it callable with the right method here.
      if (!is_callable($loader = $this->getLoader())) {
        $loader = array($loader, 'loadClass');
      }
      AnnotationRegistry::registerLoader($loader);
      $this->initialized = TRUE;
    }
  }

}
