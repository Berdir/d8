<?php

/**
 * @file
 * Contains \Drupal\TestMigrateExecutable.
 */

namespace Drupal\migrate\Tests;

use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\migrate\MigrateExecutable;

class TestMigrateExecutable extends MigrateExecutable {


  /**
   * (Fake) number of seconds elapsed since the start of the test.
   *
   * @var int
   */
  protected $timeElapsed;

  /**
   * Fake memory usage in bytes.
   *
   * @var int
   */
  protected $memoryUsage;

  /**
   * The cleared memory usage.
   */
  protected $clearedMemoryUsage;

  public function setTranslationManager(TranslationInterface $translation_manager) {
    $this->translationManager = $translation_manager;
  }

  /**
   * Allows access to protected timeOptionExceeded method.
   */
  public function timeOptionExceeded() {
    return parent::timeOptionExceeded();
  }

  /**
   * Allows access to set protected maxExecTime property.
   */
  public function setMaxExecTime($max_exec_time) {
    $this->maxExecTime = $max_exec_time;
  }

  /**
   * Allows access to protected maxExecTime property.
   */
  public function getMaxExecTime() {
    return $this->maxExecTime;
  }

  public function getSuccessesSinceFeedback() {
    return $this->successesSinceFeedback;
  }

  public function getTotalSuccesses() {
    return $this->totalSuccesses;
  }

  public function getTotalProcessed() {
    return $this->totalProcessed;
  }

  public function getProcessedSinceFeedback() {
    return $this->processedSinceFeedback;
  }

  /**
   * Allows access to protected maxExecTimeExceeded method.
   */
  public function maxExecTimeExceeded() {
    return parent::maxExecTimeExceeded();
  }

  /**
   *
   */
  public function setSource($source) {
    $this->source = $source;
  }

  /**
   * Allows access to protected sourceIdValues property.
   */
  public function setSourceIdValues($source_id_values) {
    $this->sourceIdValues = $source_id_values;
  }

  /**
   * Allows setting a fake elapsed time.
   */
  public function setTimeElapsed($time) {
    $this->timeElapsed = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimeElapsed() {
    return $this->timeElapsed;
  }

  /**
   * {@inheritdoc}
   */
  public function handleException($exception, $save = TRUE) {
    $message = $exception->getMessage();
    if ($save) {
      $this->saveMessage($message);
    }
    $this->message->display($message);
  }

  /**
   * Allows access to the protected memoryExceeded method.
   *
   * @return bool
   */
  public function memoryExceeded() {
    return parent::memoryExceeded();
  }

  /**
   * {@inheritdoc}
   */
  protected function attemptMemoryReclaim() {
    return $this->clearedMemoryUsage;
  }

  /**
   * {@inheritdoc}
   */
  protected function getMemoryUsage() {
    return $this->memoryUsage;
  }

  /**
   * Set the fake memory usage.
   */
  public function setMemoryUsage($memory_usage, $cleared_memory_usage = NULL) {
    $this->memoryUsage = $memory_usage;
    $this->clearedMemoryUsage = $cleared_memory_usage;
  }

  /**
   * Set the memory limit.
   */
  public function setMemoryLimit($memory_limit) {
    $this->memoryLimit = $memory_limit;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatSize($size) {
    return $size;
  }

}
