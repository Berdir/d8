<?php

/**
 * @file
 * Defines Drupal\Core\Session\Handler\DatabaseSessionHandler.
 */

namespace Drupal\Core\Session\Handler;

/**
 * Drupal database session handler, load and save sessions using the {sessions}
 * table throught DBTng.
 */
class DatabaseSessionHandler implements \SessionHandlerInterface {

  /**
   * @Implements SessionHandlerInterface::open().
   */
  public function open($savePath, $sessionName) {
    return TRUE;
  }

  /**
   * @Implements SessionHandlerInterface::close().
   */
  public function close() {
    return TRUE;
  }

  /**
   * @Implements SessionHandlerInterface::destroy().
   */
  public function destroy($sessionId) {
    try {
      db_delete('sessions')->condition('sid', $sessionId)->execute();
    }
    catch (\PDOException $e) {
      throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data: %s', $e->getMessage()), 0, $e);
    }

    return TRUE;
  }

  /**
   * @Implements SessionHandlerInterface::gc().
   */
  public function gc($lifetime) {
    try {
      db_delete('sessions')->condition('timestamp', time() - $lifetime, '<')->execute();
    }
    catch (\PDOException $e) {
      throw new \RuntimeException(sprintf('PDOException was thrown when trying to manipulate session data: %s', $e->getMessage()), 0, $e);
    }

    return TRUE;
  }

  /**
   * @Implements SessionHandlerInterface::read().
   */
  public function read($sessionId) {
    $data = db_query("SELECT s.* FROM {sessions} s WHERE s.sid = :sid", array(':sid' => $sessionId))->fetchObject();
    return !empty($data) ? $data->session : '';
  }

  /**
   * @Implements SessionHandlerInterface::write().
   */
  public function write($sessionId, $data) {
    try {
      db_merge('sessions')
        ->key(array(
          'sid' => $sessionId,
        ))
        ->fields(array(
          'session' => $data,
          'timestamp' => time(),
        ))
        ->execute();
    }
    catch (\PDOException $e) {
      throw new \RuntimeException(sprintf('PDOException was thrown when trying to write session data: %s', $e->getMessage()), 0, $e);
    }

    return TRUE;
  }
}
