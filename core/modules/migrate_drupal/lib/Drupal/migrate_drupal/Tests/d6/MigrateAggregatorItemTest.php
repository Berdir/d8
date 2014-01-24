<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateAggregatorItemTest.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\aggregator\Entity\Item;
use Drupal\Core\Language\Language;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

class MigrateAggregatorItemTest extends MigrateDrupalTestBase {

  static $modules = array('aggregator');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate aggregator items',
      'description'  => 'Upgrade aggregator items',
      'group' => 'Migrate Drupal',
    );
  }

  function testAggregatorItem() {
    // We need some sample data so we can use the Migration process plugin.
    $table_name = entity_load('migration', 'd6_aggregator_feed')->getIdMap()->getMapTableName();
    \Drupal::database()->insert($table_name)->fields(array(
      'sourceid1',
      'destid1',
    ))
      ->values(array(
        'sourceid1' => 1,
        'destid1' => 5,
      ))
      ->execute();

    $entity = entity_create('aggregator_feed', array(
      'fid' => 5,
      'title' => 'Drupal Core',
      'url' => 'https://groups.drupal.org/not_used/167169',
      'refresh' => 900,
      'checked' => 1389919932,
      'description' => 'Drupal Core Group feed',
    ));
    $entity->enforceIsNew();
    $entity->save();

    /** @var \Drupal\migrate\entity\Migration $migration */
    $migration = entity_load('migration', 'd6_aggregator_item');
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6AggregatorItem.php',
    );
    $this->prepare($migration, $dumps);
    $executable = new MigrateExecutable($migration, $this);
    $executable->import();

    $item = entity_load('aggregator_feed', 1);

    /** @var Item $item */
    $item = entity_load('aggregator_item', 1);
    $this->assertEqual($item->id(), 1);
    $this->assertEqual($item->getFeedId(), 5);
    $this->assertEqual($item->label(), 'This (three) weeks in Drupal Core - January 10th 2014');
    $this->assertEqual($item->getAuthor(), 'larowlan');
    $this->assertEqual($item->getDescription(), "<h2 id='new'>What's new with Drupal 8?</h2>");
    $this->assertEqual($item->getLink(), 'https://groups.drupal.org/node/395218');
    $this->assertEqual($item->getPostedTime(), 1389297196);
    $this->assertEqual($item->language()->id, Language::LANGCODE_NOT_SPECIFIED);
    $this->assertEqual($item->getGuid(), '395218 at https://groups.drupal.org');

  }

}
