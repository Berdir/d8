<?php

/**
 * @file
 * Definition of Drupal\comment\Tests\CommentTranslationUITest.
 */

namespace Drupal\comment\Tests;

use Drupal\content_translation\Tests\ContentTranslationUITest;

/**
 * Tests the Comment Translation UI.
 */
class CommentTranslationUITest extends ContentTranslationUITest {

  /**
   * The subject of the test comment.
   */
  protected $subject;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('language', 'content_translation', 'node', 'comment');

  public static function getInfo() {
    return array(
      'name' => 'Comment translation UI',
      'description' => 'Tests the basic comment translation UI.',
      'group' => 'Comment',
    );
  }

  function setUp() {
    $this->entityType = 'comment';
    $this->nodeBundle = 'article';
    $this->bundle = 'comment_article';
    $this->testLanguageSelector = FALSE;
    $this->subject = $this->randomName();
    parent::setUp();
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::setupBundle().
   */
  function setupBundle() {
    parent::setupBundle();
    $this->drupalCreateContentType(array('type' => $this->nodeBundle, 'name' => $this->nodeBundle));
    // Add a comment field to the article content type.
    comment_add_default_comment_field('node', 'article', 'comment_article');
    // Add another comment field with new bundle to page content type.
    comment_add_default_comment_field('node', 'page');
    // Mark this bundle as translatable.
    content_translation_set_config('comment', 'comment_article', 'enabled', TRUE);
    // Refresh entity info.
    entity_info_cache_clear();
    // Flush the permissions after adding the translatable comment bundle.
    $this->checkPermissions(array(), TRUE);
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::getTranslatorPermission().
   */
  protected function getTranslatorPermissions() {
    return array_merge(parent::getTranslatorPermissions(), array('post comments', 'administer comments'));
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::setupTestFields().
   */
  function setupTestFields() {
    parent::setupTestFields();
    $field = field_info_field('comment_body');
    $field['translatable'] = TRUE;
    $field->save();
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::createEntity().
   */
  protected function createEntity($values, $langcode, $bundle_name = 'comment_article') {
    if ($bundle_name == 'comment_article') {
      $node_type = 'article';
      $field_name = $bundle_name;
    }
    else {
      $node_type = 'page';
      $field_name = 'comment';
    }
    $node = $this->drupalCreateNode(array(
      'type' => $node_type,
      $field_name => array(
        array('status' => COMMENT_OPEN)
      ),
    ));
    $values['entity_id'] = $node->nid;
    $values['entity_type'] = 'node';
    $values['field_name'] = $bundle_name;
    $values['uid'] = $node->uid;
    return parent::createEntity($values, $langcode, $bundle_name);
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::getNewEntityValues().
   */
  protected function getNewEntityValues($langcode) {
    // Comment subject is not translatable hence we use a fixed value.
    return array(
      'subject' => $this->subject,
      'comment_body' => array(array('value' => $this->randomName(16))),
    ) + parent::getNewEntityValues($langcode);
  }

  /**
   * Overrides \Drupal\content_translation\Tests\ContentTranslationUITest::assertPublishedStatus().
   */
  protected function assertPublishedStatus() {
    parent::assertPublishedStatus();
    $entity = entity_load($this->entityType, $this->entityId);
    $user = $this->drupalCreateUser(array('access comments'));
    $this->drupalLogin($user);
    $languages = language_list();

    // Check that simple users cannot see unpublished field translations.
    $path = $this->controller->getViewPath($entity);
    foreach ($this->langcodes as $index => $langcode) {
      $translation = $this->getTranslation($entity, $langcode);
      $value = $this->getValue($translation, 'comment_body', $langcode);
      $this->drupalGet($path, array('language' => $languages[$langcode]));
      if ($index > 0) {
        $this->assertNoRaw($value, 'Unpublished field translation is not shown.');
      }
      else {
        $this->assertRaw($value, 'Published field translation is shown.');
      }
    }

    // Login as translator again to ensure subsequent tests do not break.
    $this->drupalLogin($this->translator);
  }

  /**
   * Tests translate link on comment content admin page.
   */
  function testTranslateLinkCommentAdminPage() {
    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'page'));
    $this->admin_user = $this->drupalCreateUser(array_merge(parent::getTranslatorPermissions(), array('access administration pages', 'administer comments')));
    $this->drupalLogin($this->admin_user);

    $cid_translatable = $this->createEntity(array(), $this->langcodes[0]);
    $cid_untranslatable = $this->createEntity(array(), $this->langcodes[0], 'comment');

    // Verify translation links.
    $this->drupalGet('admin/content/comment');
    $this->assertResponse(200);
    $this->assertLinkByHref('comment/' . $cid_translatable . '/translations');
    $this->assertNoLinkByHref('comment/' . $cid_untranslatable . '/translations');
  }

}
