<?php

/**
 * @file
 * Definition of Drupal\Core\Database\Database
 */

namespace Drupal\Core\Database;

/**
 * Primary front-controller for the database system.
 */
class Database {

  /**
   * Flag to indicate a query call should simply return NULL.
   *
   * This is used for queries that have no reasonable return value anyway, such
   * as INSERT statements to a table without a serial primary key.
   */
  const RETURN_NULL = 0;

  /**
   * Flag to indicate a query call should return the prepared statement.
   */
  const RETURN_STATEMENT = 1;

  /**
   * Flag to indicate a query call should return the number of affected rows.
   */
  const RETURN_AFFECTED = 2;

  /**
   * Flag to indicate a query call should return the "last insert id".
   */
  const RETURN_INSERT_ID = 3;

  /**
   * An nested array of all active connections. It is keyed by database name
   * and target.
   *
   * @var array
   */
  protected $connections = array();

  /**
   * A processed copy of the database connection information from settings.php.
   *
   * @var array
   */
  protected $databaseInfo = NULL;

  /**
   * A list of key/target credentials to simply ignore.
   *
   * @var array
   */
  protected $ignoreTargets = array();

  /**
   * The key of the currently active database connection.
   *
   * @var string
   */
  protected $activeKey = 'default';

  /**
   * An array of active query log objects.
   *
   * Every connection has one and only one logger object for all targets and
   * logging keys.
   *
   * array(
   *   '$db_key' => DatabaseLog object.
   * );
   *
   * @var array
   */
  protected $logs = array();

  public function __construct(array $database_info) {
    $this->parseConnectionInfo($database_info);
  }

  /**
   * Starts logging a given logging key on the specified connection.
   *
   * @param $logging_key
   *   The logging key to log.
   * @param $key
   *   The database connection key for which we want to log.
   *
   * @return Drupal\Core\Database\Log
   *   The query log object. Note that the log object does support richer
   *   methods than the few exposed through the Database class, so in some
   *   cases it may be desirable to access it directly.
   *
   * @see Drupal\Core\Database\Log
   */
  final public function startLog($logging_key, $key = 'default') {
    if (empty($this->logs[$key])) {
      $this->logs[$key] = new Log($key);

      // Every target already active for this connection key needs to have the
      // logging object associated with it.
      if (!empty($this->connections[$key])) {
        foreach ($this->connections[$key] as $connection) {
          $connection->setLogger($this->logs[$key]);
        }
      }
    }

    $this->logs[$key]->start($logging_key);
    return $this->logs[$key];
  }

  /**
   * Retrieves the queries logged on for given logging key.
   *
   * This method also ends logging for the specified key. To get the query log
   * to date without ending the logger request the logging object by starting
   * it again (which does nothing to an open log key) and call methods on it as
   * desired.
   *
   * @param $logging_key
   *   The logging key to log.
   * @param $key
   *   The database connection key for which we want to log.
   *
   * @return array
   *   The query log for the specified logging key and connection.
   *
   * @see Drupal\Core\Database\Log
   */
  final public function getLog($logging_key, $key = 'default') {
    if (empty($this->logs[$key])) {
      return NULL;
    }
    $queries = $this->logs[$key]->get($logging_key);
    $this->logs[$key]->end($logging_key);
    return $queries;
  }

  /**
   * Gets the connection object for the specified database key and target.
   *
   * @param string $target
   *   The database target name.
   * @param $key
   *   The database connection key. Defaults to NULL which means the active key.
   *
   * @return Drupal\Core\Database\Connection
   *   The corresponding connection object.
   */
  final public function getConnection($target = 'default', $key = NULL) {

    if (!isset($key)) {
      // By default, we want the active connection, set in setActiveConnection.
      $key = $this->activeKey;
    }
    // If the requested target does not exist, or if it is ignored, we fall back
    // to the default target. The target is typically either "default" or
    // "slave", indicating to use a slave SQL server if one is available. If
    // it's not available, then the default/master server is the correct server
    // to use.
    if (!empty($this->ignoreTargets[$key][$target]) || !isset($this->databaseInfo[$key][$target])) {
      $target = 'default';
    }

    if (!isset($this->connections[$key][$target])) {
      // If necessary, a new connection is opened.
      $this->connections[$key][$target] = self::openConnection($key, $target);
    }
    return $this->connections[$key][$target];
  }

  /**
   * Determines if there is an active connection.
   *
   * Note that this method will return FALSE if no connection has been
   * established yet, even if one could be.
   *
   * @return
   *   TRUE if there is at least one database connection established, FALSE
   *   otherwise.
   */
  final public function isActiveConnection() {
    return !empty($this->activeKey) && !empty($this->connections) && !empty($this->connections[$this->activeKey]);
  }

  /**
   * Sets the active connection to the specified key.
   *
   * @return
   *   The previous database connection key.
   */
  final public function setActiveConnection($key = 'default') {
    if (!empty($this->databaseInfo[$key])) {
      $old_key = $this->activeKey;
      $this->activeKey = $key;
      return $old_key;
    }
  }

  /**
   * Process the configuration file for database information.
   */
  final public function parseConnectionInfo($database_info) {
    foreach ($database_info as $index => $info) {
      foreach ($database_info[$index] as $target => $value) {
        // If there is no "driver" property, then we assume it's an array of
        // possible connections for this target. Pick one at random. That allows
        //  us to have, for example, multiple slave servers.
        if (empty($value['driver'])) {
          $database_info[$index][$target] = $database_info[$index][$target][mt_rand(0, count($database_info[$index][$target]) - 1)];
        }

        // Parse the prefix information.
        if (!isset($database_info[$index][$target]['prefix'])) {
          // Default to an empty prefix.
          $database_info[$index][$target]['prefix'] = array(
            'default' => '',
          );
        }
        elseif (!is_array($database_info[$index][$target]['prefix'])) {
          // Transform the flat form into an array form.
          $database_info[$index][$target]['prefix'] = array(
            'default' => $database_info[$index][$target]['prefix'],
          );
        }
      }
    }

    if (!is_array($this->databaseInfo)) {
      $this->databaseInfo = $database_info;
    }

    // Merge the new $database_info into the existing.
    // array_merge_recursive() cannot be used, as it would make multiple
    // database, user, and password keys in the same database array.
    else {
      foreach ($database_info as $database_key => $database_values) {
        foreach ($database_values as $target => $target_values) {
          $this->databaseInfo[$database_key][$target] = $target_values;
        }
      }
    }
  }

  /**
   * Adds database connection information for a given key/target.
   *
   * This method allows the addition of new connection credentials at runtime.
   * Under normal circumstances the preferred way to specify database
   * credentials is via settings.php. However, this method allows them to be
   * added at arbitrary times, such as during unit tests, when connecting to
   * admin-defined third party databases, etc.
   *
   * If the given key/target pair already exists, this method will be ignored.
   *
   * @param $key
   *   The database key.
   * @param $target
   *   The database target name.
   * @param $info
   *   The database connection information, as it would be defined in
   *   settings.php. Note that the structure of this array will depend on the
   *   database driver it is connecting to.
   */
  public function addConnectionInfo($key, $target, $info) {
    if (empty($this->databaseInfo[$key][$target])) {
      $this->databaseInfo[$key][$target] = $info;
    }
  }

  /**
   * Gets information on the specified database connection.
   *
   * @param $connection
   *   The connection key for which we want information.
   */
  final public function getConnectionInfo($key = 'default') {
    if (!empty($this->databaseInfo[$key])) {
      return $this->databaseInfo[$key];
    }
  }

  /**
   * Rename a connection and its corresponding connection information.
   *
   * @param $old_key
   *   The old connection key.
   * @param $new_key
   *   The new connection key.
   * @return
   *   TRUE in case of success, FALSE otherwise.
   */
  final public function renameConnection($old_key, $new_key) {
    if (!empty($this->databaseInfo[$old_key]) && empty($this->databaseInfo[$new_key])) {
      // Migrate the database connection information.
      $this->databaseInfo[$new_key] = $this->databaseInfo[$old_key];
      unset($this->databaseInfo[$old_key]);

      // Migrate over the DatabaseConnection object if it exists.
      if (isset($this->connections[$old_key])) {
        $this->connections[$new_key] = $this->connections[$old_key];
        unset($this->connections[$old_key]);
      }

      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Remove a connection and its corresponding connection information.
   *
   * @param $key
   *   The connection key.
   * @return
   *   TRUE in case of success, FALSE otherwise.
   */
  final public function removeConnection($key) {
    if (isset($this->databaseInfo[$key])) {
      self::closeConnection(NULL, $key);
      unset($this->databaseInfo[$key]);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Opens a connection to the server specified by the given key and target.
   *
   * @param $key
   *   The database connection key, as specified in settings.php. The default is
   *   "default".
   * @param $target
   *   The database target to open.
   *
   * @throws Drupal\Core\Database\ConnectionNotDefinedException
   * @throws Drupal\Core\Database\DriverNotSpecifiedException
   */
  final protected function openConnection($key, $target, $database_info = array()) {
    if (empty($this->databaseInfo)) {
      self::parseConnectionInfo($database_info);
    }

    // If the requested database does not exist then it is an unrecoverable
    // error.
    if (!isset($this->databaseInfo[$key])) {
      throw new ConnectionNotDefinedException('The specified database connection is not defined: ' . $key);
    }

    if (!$driver = $this->databaseInfo[$key][$target]['driver']) {
      throw new DriverNotSpecifiedException('Driver not specified for this database connection: ' . $key);
    }

    $driver_class = "Drupal\\Core\\Database\\Driver\\{$driver}\\Connection";
    $new_connection = new $driver_class($this->databaseInfo[$key][$target]);
    $new_connection->setTarget($target);
    $new_connection->setKey($key);

    // If we have any active logging objects for this connection key, we need
    // to associate them with the connection we just opened.
    if (!empty($this->logs[$key])) {
      $new_connection->setLogger($this->logs[$key]);
    }

    return $new_connection;
  }

  /**
   * Closes a connection to the server specified by the given key and target.
   *
   * @param $target
   *   The database target name.  Defaults to NULL meaning that all target
   *   connections will be closed.
   * @param $key
   *   The database connection key. Defaults to NULL which means the active key.
   */
  public function closeConnection($target = NULL, $key = NULL) {
    // Gets the active connection by default.
    if (!isset($key)) {
      $key = $this->activeKey;
    }
    // To close a connection, it needs to be set to NULL and removed from the
    // variable. In all cases, closeConnection() might be called for a
    // connection that was not opened yet, in which case the key is not defined
    // yet and we just ensure that the connection key is undefined.
    if (isset($target)) {
      if (isset($this->connections[$key][$target])) {
        $this->connections[$key][$target]->destroy();
        $this->connections[$key][$target] = NULL;
      }
      unset($this->connections[$key][$target]);
    }
    else {
      if (isset($this->connections[$key])) {
        foreach ($this->connections[$key] as $target => $connection) {
          $this->connections[$key][$target]->destroy();
          $this->connections[$key][$target] = NULL;
        }
      }
      unset($this->connections[$key]);
    }
  }

  /**
   * Instructs the system to temporarily ignore a given key/target.
   *
   * At times we need to temporarily disable slave queries. To do so, call this
   * method with the database key and the target to disable. That database key
   * will then always fall back to 'default' for that key, even if it's defined.
   *
   * @param $key
   *   The database connection key.
   * @param $target
   *   The target of the specified key to ignore.
   */
  public function ignoreTarget($key, $target) {
    $this->ignoreTargets[$key][$target] = TRUE;
  }
}
