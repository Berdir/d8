<?php

/**
 * @file
 * Contains \Drupal\Core\Config\BatchConfigImporter.
 */

namespace Drupal\Core\Config;

/**
 * Defines a batch configuration importer.
 *
 * @see \Drupal\Core\Config\ConfigImporter
 */
class BatchConfigImporter extends ConfigImporter {

  protected $totalToProcess = 0;

  /**
   * Initializes the config importer in preparation for processing a batch.
   */
  public function initialize() {
    $this->createExtensionChangelist();

    // Ensure that the changes have been validated.
    $this->validate();

    if (!$this->lock->acquire(static::LOCK_ID)) {
      // Another process is synchronizing configuration.
      throw new ConfigImporterException(sprintf('%s is already importing', static::LOCK_ID));
    }

    $modules = $this->getUnprocessedExtensions('module');
    foreach (array('install', 'uninstall') as $op) {
      $this->totalToProcess += count($modules[$op]);
    }
    $themes = $this->getUnprocessedExtensions('theme');
    foreach (array('enable', 'disable') as $op) {
      $this->totalToProcess += count($themes[$op]);
    }
    foreach (array('create', 'delete', 'update') as $op) {
      $this->totalToProcess += count($this->getUnprocessedConfiguration($op));
    }
  }

  /**
   * Processes batch.
   *
   * @param array $context.
   *   The batch context.
   */
  public function processBatch(array &$context) {
    $operation = $this->getNextOperation();
    if (!empty($operation)) {
      if (!empty($operation['type'])) {
        $this->processExtension($operation['type'], $operation['op'], $operation['name']);
        $this->recalculateChangelist = TRUE;
      }
      else {
        if ($this->recalculateChangelist) {
          $current_total = 0;
          foreach (array('create', 'delete', 'update') as $op) {
            $current_total += count($this->getUnprocessedConfiguration($op));
          }
          $this->storageComparer->reset();
          // This will cause the changelist to be calculated.
          $new_total = 0;
          foreach (array('create', 'delete', 'update') as $op) {
            $new_total += count($this->getUnprocessedConfiguration($op));
          }
          $this->totalToProcess = $this->totalToProcess = $current_total + $new_total;
          $operation = $this->getNextOperation();
          $this->recalculateChangelist = FALSE;
        }
        // Rebuilding the changelist change remove all changes.
        if ($operation !== FALSE) {
          $this->processConfiguration($operation['op'], $operation['name']);
        }
      }
      $context['message'] = t('Synchronizing @name.', array('@name' => $operation['name']));
      $context['finished'] = $this->batchProgress();
    }
    else {
      $context['finished'] = 1;
    }
    if ($context['finished'] >= 1) {
      $this->eventDispatcher->dispatch(ConfigEvents::IMPORT, new ConfigImporterEvent($this));
      // The import is now complete.
      $this->lock->release(static::LOCK_ID);
      $this->reset();
    }
  }

  /**
   * Gets percentage of progress made.
   *
   * @return float
   *   The percentage of progress made expressed as a float between 0 and 1.
   */
  protected function batchProgress() {
    $processed_count = count($this->processedExtensions['module']['install']) + count($this->processedExtensions['module']['uninstall']);
    $processed_count += count($this->processedExtensions['theme']['disable']) + count($this->processedExtensions['theme']['enable']);
    $processed_count += count($this->processedConfiguration['create']) + count($this->processedConfiguration['delete']) + count($this->processedConfiguration['update']);
    return $processed_count / $this->totalToProcess;
  }

  /**
   * Gets the next operation to perform.
   *
   * We process extensions before we process configuration files.
   *
   * @return array|bool
   *   An array containing the next operation and configuration name to perform
   *   it on. If there is nothing left to do returns FALSE;
   */
  protected function getNextOperation() {
    foreach (array('install', 'uninstall') as $op) {
      $modules = $this->getUnprocessedExtensions('module');
      if (!empty($modules[$op])) {
        return array(
          'op' => $op,
          'type' => 'module',
          'name' => array_shift($modules[$op]),
        );
      }
    }
    foreach (array('enable', 'disable') as $op) {
      $themes = $this->getUnprocessedExtensions('theme');
      if (!empty($themes[$op])) {
        return array(
          'op' => $op,
          'type' => 'theme',
          'name' => array_shift($themes[$op]),
        );
      }
    }
    foreach (array('create', 'delete', 'update') as $op) {
      $config_names = $this->getUnprocessedConfiguration($op);
      if (!empty($config_names)) {
        return array(
          'op' => $op,
          'name' => array_shift($config_names),
        );
      }
    }
    return FALSE;
  }
}
