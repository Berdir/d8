<?php

/**
 * @file
 * Definition of Drupal\views\Tests\ViewsDataTest.
 */

namespace Drupal\views\Tests;

use Drupal\Core\Cache\MemoryCounterBackend;
use Drupal\views\ViewsDataCache;

/**
 * Tests the fetching of views data.
 *
 * @see hook_views_data
 */
class ViewsDataTest extends ViewUnitTestBase {

  /**
   * Stores the views data cache service used by this test.
   *
   * @var \Drupal\views\ViewsDataCache
   */
  protected $viewsDataCache;

  /**
   * Stores a count for hook_views_data being invoked.
   *
   * @var int
   */
  protected $count = 0;

  /**
   * The memory backend to use for the test.
   *
   * @var \Drupal\Core\Cache\MemoryCounterBackend
   */
  protected $memoryCounterBackend;

  public static function getInfo() {
    return array(
      'name' => 'Table Data',
      'description' => 'Tests the fetching of views data.',
      'group' => 'Views',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->memoryCounterBackend = new MemoryCounterBackend('views_info');
    $this->state = $this->container->get('state');

    $this->initViewsDataCache();
  }

  /**
   * Tests the views.views_data service.
   *
   * @see \Drupal\views\ViewsDataCache
   */
  public function testViewsFetchData() {
    $table_name = 'views_test_data';
    $random_table_name = $this->randomName();
    // Invoke expected data directly from hook_views_data implementations.
    $expected_data = $this->container->get('module_handler')->invokeAll('views_data');

    // Verify that views_test_data_views_data() has only been called once after
    // calling clear().
    $this->startCount();
    $this->viewsDataCache->get();
    // Test views data has been invoked.
    $this->assertCountIncrement();
    // Clear the storage/cache.
    $this->viewsDataCache->clear();
    // Get the data again.
    $this->viewsDataCache->get();
    $this->viewsDataCache->get($table_name);
    $this->viewsDataCache->get($random_table_name);
    // Verify that view_test_data_views_data() has run once.
    $this->assertCountIncrement();

    // Get the data again.
    $this->viewsDataCache->get();
    $this->viewsDataCache->get($table_name);
    $this->viewsDataCache->get($random_table_name);
    // Verify that view_test_data_views_data() has not run again.
    $this->assertCountIncrement(FALSE);

    // Clear the views data, and test all table data.
    $this->viewsDataCache->clear();
    $this->startCount();
    $data = $this->viewsDataCache->get();
    $this->assertEqual($data, $expected_data, 'Make sure fetching all views data by works as expected.');
    // Views data should be invoked once.
    $this->assertCountIncrement();
    // Calling get() again, the count for this table should stay the same.
    $data = $this->viewsDataCache->get();
    $this->assertEqual($data, $expected_data, 'Make sure fetching all cached views data works as expected.');
    $this->assertCountIncrement(FALSE);

    // Clear the views data, and test data for a specific table.
    $this->viewsDataCache->clear();
    $this->startCount();
    $data = $this->viewsDataCache->get($table_name);
    $this->assertEqual($data, $expected_data[$table_name], 'Make sure fetching views data by table works as expected.');
    // Views data should be invoked once.
    $this->assertCountIncrement();
    // Calling get() again, the count for this table should stay the same.
    $data = $this->viewsDataCache->get($table_name);
    $this->assertEqual($data, $expected_data[$table_name], 'Make sure fetching cached views data by table works as expected.');
    $this->assertCountIncrement(FALSE);
    // Test that this data is present if all views data is returned.
    $data = $this->viewsDataCache->get();
    $this->assertTrue(isset($data[$table_name]), 'Make sure the views_test_data info appears in the total views data.');
    $this->assertEqual($data[$table_name], $expected_data[$table_name], 'Make sure the views_test_data has the expected values.');

    // Clear the views data, and test data for an invalid table.
    $this->viewsDataCache->clear();
    $this->startCount();
    // All views data should be requested on the first try.
    $data = $this->viewsDataCache->get($random_table_name);
    $this->assertEqual($data, array(), 'Make sure fetching views data for an invalid table returns an empty array.');
    $this->assertCountIncrement();
    // Test no data is rebuilt when requesting an invalid table again.
    $data = $this->viewsDataCache->get($random_table_name);
    $this->assertEqual($data, array(), 'Make sure fetching views data for an invalid table returns an empty array.');
    $this->assertCountIncrement(FALSE);
  }

  /**
   * Ensures that cache entries are only set and get when necessary.
   */
  public function testCacheRequests() {
    // Request the same table 5 times. The caches are empty at this point, so
    // what will happen is that it will first check for a cache entry for the
    // given table, get a cache miss, then try the cache entry for all tables,
    // which does not exist yet either. As a result, it rebuilds the information
    // and writes a cache entry for all tables and the requested table.
    $table_name = 'views_test_data';
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get($table_name);
    }

    // Assert cache set and get calls.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:views_test_data:en'), 1, 'Requested the cache for the table-specific cache entry.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 1, 'Requested the cache for all tables.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:views_test_data:en'), 1, 'Wrote the cache for the requested once.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 1, 'Wrote the cache for the all tables once.');

    // Re-initialize the views data cache to simulate a new request and repeat.
    // We have a warm cache now, so this will only request the tables-specific
    // cache entry and return that.
    $this->initViewsDataCache();
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get($table_name);
    }

    // Assert cache set and get calls.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:views_test_data:en'), 1, 'Requested the cache for the table-specific cache entry.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 0, 'Did not request to load the cache entry for all tables.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:views_test_data:en'), 0, 'Did not write the cache for the requested table.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 0, 'Did not write the cache for all tables.');

    // Re-initialize the views data cache to simulate a new request and request
    // a different table. This will fail to get a table specific cache entry,
    // load the cache entry for all tables and save a cache entry for this table
    // but not all.
    $this->initViewsDataCache();
    $another_table_name = 'views';
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get($another_table_name);
    }

    // Assert cache set and get calls.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:views:en'), 1, 'Requested the cache for the table-specific cache entry.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 1, 'Requested the cache for all tables.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:views:en'), 1, 'Wrote the cache for the requested once.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 0, 'Did not write the cache for all tables.');

    // Re-initialize the views data cache to simulate a new request and request
    // a non-existing table. This will result in the same cache requests as we
    // explicitly write an empty cache entry for non-existing tables to avoid
    // unecessary requests in those situations. We do have to load the cache
    // entry for all tables to check if the table does exist or not.
    $this->initViewsDataCache();
    $non_existing_table = $this->randomName();
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get($non_existing_table);
    }

    // Assert cache set and get calls.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', "views_data:$non_existing_table:en"), 1, 'Requested the cache for the table-specific cache entry.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 1, 'Requested the cache for all tables.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', "views_data:$non_existing_table:en"), 1, 'Wrote the cache for the requested once.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 0, 'Did not write the cache for all tables.');

    // Re-initialize the views data cache to simulate a new request and request
    // the same non-existing table. This will load the table-specific cache
    // entry and return the stored empty array for that.
    $this->initViewsDataCache();
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get($non_existing_table);
    }

    // Assert cache set and get calls.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', "views_data:$non_existing_table:en"), 1, 'Requested the cache for the table-specific cache entry.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 0, 'Did not request to load the cache entry for all tables.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', "views_data:$non_existing_table:en"), 0, 'Did not write the cache for the requested table.');
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 0, 'Did not write the cache for all tables.');

    // Re-initialize the views data cache and repeat with no specified table.
    // This should only load the cache entry for all tables.
    $this->initViewsDataCache();
    for ($i = 0; $i < 5; $i++) {
      $this->viewsDataCache->get();
    }

    // This only requested the full information. No other cache requests should
    // have been made.
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:views_test_data:en'), 0);
    $this->assertEqual($this->memoryCounterBackend->getCounter('get', 'views_data:en'), 1);
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:views_test_data:en'), 0);
    $this->assertEqual($this->memoryCounterBackend->getCounter('set', 'views_data:en'), 0);

  }

  /**
   * Initializes a new ViewsDataCache instance and resets the cache backend.
   */
  protected function initViewsDataCache() {
    $this->memoryCounterBackend->resetCounter();
    $this->viewsDataCache = new ViewsDataCache($this->memoryCounterBackend, $this->container->get('config.factory'), $this->container->get('module_handler'));
  }

  /**
   * Starts a count for hook_views_data being invoked.
   */
  protected function startCount() {
    $count = $this->state->get('views_test_data_views_data_count');
    $this->count = isset($count) ? $count : 0;
  }

  /**
   * Asserts that the count for hook_views_data either equal or has increased.
   *
   * @param bool $equal
   *   Whether to assert that the count should be equal. Defaults to FALSE.
   */
  protected function assertCountIncrement($increment = TRUE) {
    if ($increment) {
      // If an incremented count is expected, increment this now.
      $this->count++;
      $message = 'hook_views_data has been invoked.';
    }
    else {
      $message = 'hook_views_data has not been invoked';
    }

    $this->assertEqual($this->count, $this->state->get('views_test_data_views_data_count'), $message);
  }

  /**
   * Overrides Drupal\views\Tests\ViewTestBase::viewsData().
   */
  protected function viewsData() {
    $data = parent::viewsData();

    // Tweak the views data to have a base for testing views_fetch_fields().
    unset($data['views_test_data']['id']['field']);
    unset($data['views_test_data']['name']['argument']);
    unset($data['views_test_data']['age']['filter']);
    unset($data['views_test_data']['job']['sort']);
    $data['views_test_data']['created']['area']['id'] = 'text';
    $data['views_test_data']['age']['area']['id'] = 'text';
    $data['views_test_data']['age']['area']['sub_type'] = 'header';
    $data['views_test_data']['job']['area']['id'] = 'text';
    $data['views_test_data']['job']['area']['sub_type'] = array('header', 'footer');


    return $data;
  }


  /**
   * Tests the views_fetch_fields function().
   */
  public function testViewsFetchFields() {
    $this->enableModules(array('views_ui'));
    $this->container->get('module_handler')->loadInclude('views_ui', 'inc', 'admin');

    $expected = array(
      'field' => array(
        'name',
        'age',
        'job',
        'created',
      ),
      'argument' => array(
        'id',
        'age',
        'job',
        'created',
      ),
      'filter' => array(
        'id',
        'name',
        'job',
        'created',
      ),
      'sort' => array(
        'id',
        'name',
        'age',
        'created',
      ),
      'area' => array(
        'created',
        'job',
        'age'
      ),
      'header' => array(
        'created',
        'job',
        'age'
      ),
      'footer' => array(
        'created',
        'job',
      ),
    );

    $handler_types = array('field', 'argument', 'filter', 'sort', 'area');
    foreach ($handler_types as $handler_type) {
      $fields = views_fetch_fields('views_test_data', $handler_type);
      $expected_keys = array_walk($expected[$handler_type], function(&$item) {
        $item = "views_test_data.$item";
      });
      $this->assertEqual($expected_keys, array_keys($fields), format_string('Handlers of type @handler_type are listed as expected.', array('@handler_type' => $handler_type)));
    }

    // Check for subtype filtering, so header and footer.
    foreach (array('header', 'footer') as $sub_type) {
      $fields = views_fetch_fields('views_test_data', 'area', FALSE, $sub_type);

      $expected_keys = array_walk($expected[$sub_type], function(&$item) {
        $item = "views_test_data.$item";
      });
      $this->assertEqual($expected_keys, array_keys($fields), format_string('Sub_type @sub_type is filtered as expected.', array('@sub_type' => $sub_type)));
    }
  }

  /**
   * Tests the fetchBaseTables() method.
   */
  public function testFetchBaseTables() {
    // Enabled node module so there is more than 1 base table to test.
    $this->enableModules(array('node'));
    $data = $this->viewsDataCache->get();
    $base_tables = $this->viewsDataCache->fetchBaseTables();

    // Test the number of tables returned and their order.
    $this->assertEqual(count($base_tables), 3, 'The correct amount of base tables were returned.');
    $this->assertIdentical(array_keys($base_tables), array('node', 'node_revision', 'views_test_data'), 'The tables are sorted as expected.');

    // Test the values returned for each base table.
    $defaults = array(
      'title' => '',
      'help' => '',
      'weight' => 0,
    );
    foreach ($base_tables as $base_table => $info) {
      // Merge in default values as in fetchBaseTables().
      $expected = $data[$base_table]['table']['base'] += $defaults;
      foreach ($defaults as $key => $default) {
        $this->assertEqual($info[$key], $expected[$key]);
      }
    }
  }

}
