<?php

/**
 * @file
 * Definition of Drupal\Core\Config\FileStorage.
 */

namespace Drupal\Core\Config;

use Drupal\Component\Yaml\Yaml;

/**
 * Defines the file storage controller.
 */
class FileStorage implements StorageInterface {

  /**
   * The filesystem path for configuration objects.
   *
   * @var string
   */
  protected $directory = '';

  /**
   * A YAML instance.
   *
   * @var \Drupal\Component\Yaml\YamlInterface
   */
  protected $yaml;

  /**
   * Constructs a new FileStorage controller.
   *
   * @param string $directory
   *   A directory path to use for reading and writing of configuration files.
   */
  public function __construct($directory) {
    $this->directory = $directory;
  }

  /**
   * Returns the path to the configuration file.
   *
   * @return string
   *   The path to the configuration file.
   */
  public function getFilePath($name) {
    return $this->directory . '/' . $name . '.' . static::getFileExtension();
  }

  /**
   * Returns the file extension used by the file storage for all configuration files.
   *
   * @return string
   *   The file extension.
   */
  public static function getFileExtension() {
    return 'yml';
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::exists().
   */
  public function exists($name) {
    return file_exists($this->getFilePath($name));
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::read().
   *
   * @throws Symfony\Component\Yaml\Exception\ParseException
   */
  public function read($name) {
    if (!$this->exists($name)) {
      return FALSE;
    }
    $data = file_get_contents($this->getFilePath($name));
    // @todo Yaml throws a ParseException on invalid data. Is it expected to be
    //   caught or not?
    $data = $this->decode($data);
    return $data;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::write().
   *
   * @throws Symfony\Component\Yaml\Exception\DumpException
   * @throws Drupal\Core\Config\StorageException
   */
  public function write($name, array $data) {
    $data = $this->encode($data);
    $status = @file_put_contents($this->getFilePath($name), $data);
    if ($status === FALSE) {
      throw new StorageException('Failed to write configuration file: ' . $this->getFilePath($name));
    }
    return TRUE;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::delete().
   */
  public function delete($name) {
    if (!$this->exists($name)) {
      if (!file_exists($this->directory)) {
        throw new StorageException($this->directory . '/ not found.');
      }
      return FALSE;
    }
    return drupal_unlink($this->getFilePath($name));
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::rename().
   */
  public function rename($name, $new_name) {
    $status = @rename($this->getFilePath($name), $this->getFilePath($new_name));
    if ($status === FALSE) {
      throw new StorageException('Failed to rename configuration file from: ' . $this->getFilePath($name) . ' to: ' . $this->getFilePath($new_name));
    }
    return TRUE;
  }

  /**
   * Gets the YAML instance.
   *
   * @return \Drupal\Component\Yaml\YamlInterface
   */
  protected function getYaml() {
    if (!isset($this->yaml)) {
      $this->yaml = new Yaml();
    }
    return $this->yaml;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::encode().
   *
   * @throws Symfony\Component\Yaml\Exception\DumpException
   */
  public function encode($data) {
    return $this->getYaml()->dump($data);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::decode().
   *
   * @throws Symfony\Component\Yaml\Exception\ParseException
   */
  public function decode($raw) {
    $data = $this->getYaml()->parse($raw);
    // A simple string is valid YAML for any reason.
    if (!is_array($data)) {
      return FALSE;
    }
    return $data;
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::listAll().
   */
  public function listAll($prefix = '') {
    // glob() silently ignores the error of a non-existing search directory,
    // even with the GLOB_ERR flag.
    if (!file_exists($this->directory)) {
      throw new StorageException($this->directory . '/ not found.');
    }
    $extension = '.' . static::getFileExtension();
    $files = glob($this->directory . '/' . $prefix . '*' . $extension);
    $clean_name = function ($value) use ($extension) {
      return basename($value, $extension);
    };
    return array_map($clean_name, $files);
  }

  /**
   * Implements Drupal\Core\Config\StorageInterface::deleteAll().
   */
  public function deleteAll($prefix = '') {
    $success = TRUE;
    $files = $this->listAll($prefix);
    foreach ($files as $name) {
      if (!$this->delete($name) && $success) {
        $success = FALSE;
      }
    }

    return $success;
  }
}
