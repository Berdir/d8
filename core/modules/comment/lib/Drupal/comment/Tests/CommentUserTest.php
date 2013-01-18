<?php

/**
 * @file
 * Contains Drupal\comment\Tests\CommentUserTest.
 */

namespace Drupal\comment\Tests;
use Drupal\comment\Plugin\Core\Entity\Comment;
use Drupal\simpletest\WebTestBase;

/**
 * Tests basic comment functionality against a user entity.
 */
class CommentUserTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('comment', 'user');

  /**
   * An administrative user with permission to configure comment settings.
   *
   * @var Drupal\user\User
   */
  protected $admin_user;

  /**
   * A normal user with permission to post comments.
   *
   * @var Drupal\user\User
   */
  protected $web_user;

  /**
   * Provides test information.
   */
  public static function getInfo() {
    return array(
      'name' => 'Comment user tests',
      'description' => 'Test commenting on users.',
      'group' => 'Comment',
    );
  }

  function setUp() {
    parent::setUp();

    // Create comment field on user bundle.
    comment_add_default_comment_field('user', 'user');

    // Create two test users.
    $this->admin_user = $this->drupalCreateUser(array(
      'administer comments',
      'skip comment approval',
      'post comments',
      'access comments',
      'access content',
      'administer users',
      'access user profiles',
     ));
    $this->web_user = $this->drupalCreateUser(array(
      'access comments',
      'post comments',
      'edit own comments',
      'post comments',
      'skip comment approval',
      'access content',
      'access user profiles',
    ));

    // Enable anonymous and authenticated user comments.
    user_role_grant_permissions(DRUPAL_ANONYMOUS_RID, array(
      'access comments',
      'post comments',
      'skip comment approval',
    ));
    user_role_grant_permissions(DRUPAL_AUTHENTICATED_RID, array(
      'access comments',
      'post comments',
      'skip comment approval',
    ));

    $this->web_user->comment = array(LANGUAGE_NOT_SPECIFIED => array(array('comment' => COMMENT_OPEN)));
    $this->web_user->save();
  }

  /**
   * Posts a comment.
   *
   * @param Drupal\user\User|null $account
   *   User to post comment on or NULL to post to the previusly loaded page.
   * @param $comment
   *   Comment body.
   * @param $subject
   *   Comment subject.
   * @param $contact
   *   Set to NULL for no contact info, TRUE to ignore success checking, and
   *   array of values to set contact info.
   */
  function postComment($account, $comment, $subject = '', $contact = NULL) {
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $edit = array();
    $edit['comment_body[' . $langcode . '][0][value]'] = $comment;

    $instance = field_info_instance('user', 'comment', 'user');
    $preview_mode = $instance['settings']['comment']['comment_preview'];
    $subject_mode = $instance['settings']['comment']['comment_subject_field'];

    // Must get the page before we test for fields.
    if ($account !== NULL) {
      $this->drupalGet('comment/reply/user/' . $account->uid . '/comment');
    }

    if ($subject_mode == TRUE) {
      $edit['subject'] = $subject;
    }
    else {
      $this->assertNoFieldByName('subject', '', 'Subject field not found.');
    }

    if ($contact !== NULL && is_array($contact)) {
      $edit += $contact;
    }
    switch ($preview_mode) {
      case DRUPAL_REQUIRED:
        // Preview required so no save button should be found.
        $this->assertNoFieldByName('op', t('Save'), 'Save button not found.');
        $this->drupalPost(NULL, $edit, t('Preview'));
        // Don't break here so that we can test post-preview field presence and
        // function below.
      case DRUPAL_OPTIONAL:
        $this->assertFieldByName('op', t('Preview'), 'Preview button found.');
        $this->assertFieldByName('op', t('Save'), 'Save button found.');
        $this->drupalPost(NULL, $edit, t('Save'));
        break;

      case DRUPAL_DISABLED:
        $this->assertNoFieldByName('op', t('Preview'), 'Preview button not found.');
        $this->assertFieldByName('op', t('Save'), 'Save button found.');
        $this->drupalPost(NULL, $edit, t('Save'));
        break;
    }
    $match = array();
    // Get comment ID
    preg_match('/#comment-([0-9]+)/', $this->getURL(), $match);

    // Get comment.
    if ($contact !== TRUE) { // If true then attempting to find error message.
      if ($subject) {
        $this->assertText($subject, 'Comment subject posted.');
      }
      $this->assertText($comment, 'Comment body posted.');
      $this->assertTrue((!empty($match) && !empty($match[1])), 'Comment id found.');
    }

    if (isset($match[1])) {
      return comment_load($match[1]);
    }
  }

  /**
   * Checks current page for specified comment.
   *
   * @param Drupal\comment\Plugin\Core\Entity\comment $comment
   *   The comment object.
   * @param boolean $reply
   *   Boolean indicating whether the comment is a reply to another comment.
   *
   * @return boolean
   *   Boolean indicating whether the comment was found.
   */
  function commentExists(Comment $comment = NULL, $reply = FALSE) {
    if ($comment) {
      $regex = '/' . ($reply ? '<div class="indented">(.*?)' : '');
      $regex .= '<a id="comment-' . $comment->id() . '"(.*?)'; // Comment anchor.
      $regex .= $comment->subject->value . '(.*?)'; // Match subject.
      $regex .= $comment->comment->value . '(.*?)'; // Match comment.
      $regex .= '/s';

      return (boolean)preg_match($regex, $this->drupalGetContent());
    }
    else {
      return FALSE;
    }
  }

  /**
   * Deletes a comment.
   *
   * @param Drupal\comment\Comment $comment
   *   Comment to delete.
   */
  function deleteComment(Comment $comment) {
    $this->drupalPost('comment/' . $comment->id() . '/delete', array(), t('Delete'));
    $this->assertText(t('The comment and all its replies have been deleted.'), 'Comment deleted.');
  }

  /**
   * Checks whether the commenter's contact information is displayed.
   *
   * @return boolean
   *   Contact info is available.
   */
  function commentContactInfoAvailable() {
    return preg_match('/(input).*?(name="name").*?(input).*?(name="mail").*?(input).*?(name="homepage")/s', $this->drupalGetContent());
  }

  /**
   * Performs the specified operation on the specified comment.
   *
   * @param object $comment
   *   Comment to perform operation on.
   * @param string $operation
   *   Operation to perform.
   * @param boolean $aproval
   *   Operation is found on approval page.
   */
  function performCommentOperation($comment, $operation, $approval = FALSE) {
    $edit = array();
    $edit['operation'] = $operation;
    $edit['comments[' . $comment->id() . ']'] = TRUE;
    $this->drupalPost('admin/content/comment' . ($approval ? '/approval' : ''), $edit, t('Update'));

    if ($operation == 'delete') {
      $this->drupalPost(NULL, array(), t('Delete comments'));
      $this->assertRaw(format_plural(1, 'Deleted 1 comment.', 'Deleted @count comments.'), format_string('Operation "@operation" was performed on comment.', array('@operation' => $operation)));
    }
    else {
      $this->assertText(t('The update has been performed.'), format_string('Operation "@operation" was performed on comment.', array('@operation' => $operation)));
    }
  }

  /**
   * Gets the comment ID for an unapproved comment.
   *
   * @param string $subject
   *   Comment subject to find.
   *
   * @return integer
   *   Comment id.
   */
  function getUnapprovedComment($subject) {
    $this->drupalGet('admin/content/comment/approval');
    preg_match('/href="(.*?)#comment-([^"]+)"(.*?)>(' . $subject . ')/', $this->drupalGetContent(), $match);

    return $match[2];
  }

  /**
   * Tests anonymous comment functionality.
   */
  function testCommentUser() {
    $this->drupalLogin($this->admin_user);

    // Post a comment.
    $comment1 = $this->postComment($this->web_user, $this->randomName(), $this->randomName());
    $this->assertTrue($this->commentExists($comment1), 'Comment on web user exists.');

    // Unpublish comment.
    $this->performCommentOperation($comment1, 'unpublish');

    $this->drupalGet('admin/content/comment/approval');
    $this->assertRaw('comments[' . $comment1->id . ']', 'Comment was unpublished.');

    // Publish comment.
    $this->performCommentOperation($comment1, 'publish', TRUE);

    $this->drupalGet('admin/content/comment');
    $this->assertRaw('comments[' . $comment1->id . ']', 'Comment was published.');

    // Delete comment.
    $this->performCommentOperation($comment1, 'delete');

    $this->drupalGet('admin/content/comment');
    $this->assertNoRaw('comments[' . $comment1->id . ']', 'Comment was deleted.');

    // Post another comment.
    $comment1 = $this->postComment($this->web_user, $this->randomName(), $this->randomName());
    $this->assertTrue($this->commentExists($comment1), 'Comment on web user exists.');

    // Check comment was found.
    $this->drupalGet('admin/content/comment');
    $this->assertRaw('comments[' . $comment1->id . ']', 'Comment was published.');

    $this->drupalLogout();

    // Reset.
    user_role_change_permissions(DRUPAL_ANONYMOUS_RID, array(
      'access comments' => FALSE,
      'post comments' => FALSE,
      'skip comment approval' => FALSE,
      'access user profiles' => TRUE,
    ));

    // Attempt to view comments while disallowed.
    // NOTE: if authenticated user has permission to post comments, then a
    // "Login or register to post comments" type link may be shown.
    $this->drupalGet('user/' . $this->web_user->uid);
    $this->assertNoPattern('@<h2[^>]*>Comments</h2>@', 'Comments were not displayed.');
    $this->assertNoLink('Add new comment', 'Link to add comment was found.');

    // Attempt to view user-comment form while disallowed.
    $this->drupalGet('comment/reply/user/' . $this->web_user->uid . '/comment');
    $this->assertText('You are not authorized to post comments', 'Error attempting to post comment.');
    $this->assertNoFieldByName('subject', '', 'Subject field not found.');
    $langcode = LANGUAGE_NOT_SPECIFIED;
    $this->assertNoFieldByName("comment_body[$langcode][0][value]", '', 'Comment field not found.');

    user_role_change_permissions(DRUPAL_ANONYMOUS_RID, array(
      'access comments' => TRUE,
      'post comments' => FALSE,
      'access user profiles' => TRUE,
      'skip comment approval' => FALSE,
    ));
    // Ensure the page cache is flushed.
    drupal_flush_all_caches();
    $this->drupalGet('user/' . $this->web_user->uid);
    $this->assertPattern('@<h2[^>]*>Comments</h2>@', 'Comments were displayed.');
    $this->assertLink('Log in', 1, 'Link to log in was found.');
    $this->assertLink('register', 1, 'Link to register was found.');

    user_role_change_permissions(DRUPAL_ANONYMOUS_RID, array(
      'access comments' => FALSE,
      'post comments' => TRUE,
      'skip comment approval' => TRUE,
      'access user profiles' => TRUE,
    ));
    $this->drupalGet('user/' . $this->web_user->uid);
    $this->assertNoPattern('@<h2[^>]*>Comments</h2>@', 'Comments were not displayed.');
    $this->assertFieldByName('subject', '', 'Subject field found.');
    $this->assertFieldByName("comment_body[$langcode][0][value]", '', 'Comment field found.');

    $this->drupalGet('comment/reply/user/' . $this->web_user->uid . '/comment/' . $comment1->id);
    $this->assertText('You are not authorized to view comments', 'Error attempting to post reply.');
    $this->assertNoText($comment1->subject->value, 'Comment not displayed.');
  }

}
