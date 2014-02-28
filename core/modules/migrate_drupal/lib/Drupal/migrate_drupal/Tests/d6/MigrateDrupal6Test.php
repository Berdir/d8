<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\d6\MigrateDrupal6Test.
 */

namespace Drupal\migrate_drupal\Tests\d6;

use Drupal\migrate_drupal\Tests\MigrateDrupalTestBase;

/**
 * Test the complete Drupal 6 migration.
 */
class MigrateDrupal6Test extends MigrateDrupalTestBase{

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name'  => 'Migrate Drupal 6',
      'description'  => 'Test every Drupal 6 migration',
      'group' => 'Migrate Drupal',
    );
  }

  /**
   * Test the complete Drupal 6 migration.
   */
  public function testDrupal6() {
    $path = drupal_get_path('module', 'migrate_drupal');
    $dumps = array(
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6ActionSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6AggregatorFeed.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6AggregatorItem.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6AggregatorSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Block.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6BookSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Box.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Comment.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6CommentVariable.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6ContactCategory.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6ContactSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6DateFormat.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6DblogSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldInstance.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FieldSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6File.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FileSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6FilterFormat.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6ForumSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6LocaleSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Menu.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6MenuSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeBodyInstance.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Node.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeRevision.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6NodeType.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SearchPage.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SearchSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SimpletestSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6StatisticsSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SyslogSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemCron.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemFile.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemFilter.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemImageGd.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemImage.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemMaintenance.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemPerformance.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemRss.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemSite.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6SystemTheme.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomySettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomyTerm.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TaxonomyVocabulary.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TermNode.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6TextSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UpdateSettings.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UploadInstance.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6Upload.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UrlAlias.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserMail.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6User.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserProfileFields.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6UserRole.php',
      $path . '/lib/Drupal/migrate_drupal/Tests/Dump/Drupal6VocabularyField.php',
    );
    $this->loadDumps($dumps);
  }

}
