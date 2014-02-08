<?php

/**
 * @file
 * Contains \Drupal\editor\Tests\editor\EditorXssFilter\StandardTest.
 */

namespace Drupal\editor\Tests\editor\EditorXssFilter;

use Drupal\Tests\UnitTestCase;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Tests the standard text editor XSS filter.
 *
 * @group Drupal
 * @group Editor
 *
 * @see \Drupal\editor\EditorXssFilter\Standard
 */
class StandardTest extends UnitTestCase {

  /**
   * The tested text editor XSS filter.
   *
   * @var \Drupal\editor\EditorXssFilter\Standard
   */
  protected $editorXssFilterClass;

  /**
   * The mocked text format configuration entity.
   *
   * @var \Drupal\filter\Entity\FilterFormat|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $format;

  public static function getInfo() {
    return array(
      'name' => 'Standard text editor XSS filter test',
      'description' => 'Unit test of standard text editor XSS filter.',
      'group' => 'Text Editor'
    );
  }

  protected function setUp() {
    $this->editorXssFilterClass = '\Drupal\editor\EditorXssFilter\Standard';

    // Mock text format configuration entity object.
    $this->format = $this->getMockBuilder('\Drupal\filter\Entity\FilterFormat')
      ->disableOriginalConstructor()
      ->getMock();
    $this->format->expects($this->any())
      ->method('getFilterTypes')
      ->will($this->returnValue(array(FilterInterface::TYPE_HTML_RESTRICTOR)));
    $restrictions = array(
      'allowed' => array(
        'p' => TRUE,
        'a' => TRUE,
        '*' => array(
          'style' => FALSE,
          'on*' => FALSE,
        ),
      ),
    );
    $this->format->expects($this->any())
      ->method('getHtmlRestrictions')
      ->will($this->returnValue($restrictions));
  }

  /**
   * Provides test data for testFilterXss().
   *
   * \Drupal\editor\EditorXssFilter\Standard uses
   * Drupal\Component\Utility\Xss. See \Drupal\Tests\Component\Utility\XssTest::testBlacklistMode()
   * for more detailed test coverage.
   *
   * @see \Drupal\editor\Tests\editor\EditorXssFilter\StandardTest::testFilterXss()
   * @see \Drupal\Tests\Component\Utility\XssTest::testBlacklistMode()
   */
  public function providerTestFilterXss() {
    $data = array();
    $data[] = array('<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown>', '<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown>');
    $data[] = array('<p style="color:red">Hello, world!</p><unknown>Pink Fairy Armadillo</unknown>', '<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown>');
    $data[] = array('<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown><script>alert("evil");</script>', '<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown>alert("evil");');
    $data[] = array('<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown><a href="javascript:alert(1)">test</a>', '<p>Hello, world!</p><unknown>Pink Fairy Armadillo</unknown><a href="alert(1)">test</a>');

    // All cases listed on https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet

    // No Filter Evasion.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#No_Filter_Evasion
    $data[] = array('<SCRIPT SRC=http://ha.ckers.org/xss.js></SCRIPT>', '');

    // Image XSS using the JavaScript directive.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Image_XSS_using_the_JavaScript_directive
    $data[] = array('<IMG SRC="javascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // No quotes and no semicolon.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#No_quotes_and_no_semicolon
    $data[] = array('<IMG SRC=javascript:alert(\'XSS\')>', '<IMG>');

    // Case insensitive XSS attack vector.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Case_insensitive_XSS_attack_vector
    $data[] = array('<IMG SRC=JaVaScRiPt:alert(\'XSS\')>', '<IMG>');

    // HTML entities.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#HTML_entities
    $data[] = array('<IMG SRC=javascript:alert("XSS")>', '<IMG>');

    // Grave accent obfuscation.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Grave_accent_obfuscation
    $data[] = array('<IMG SRC=`javascript:alert("RSnake says, \'XSS\'")`>', '<IMG>');

    // Malformed A tags.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Malformed_A_tags
    $data[] = array('<a onmouseover="alert(document.cookie)">xxs link</a>', '<a>xxs link</a>');
    $data[] = array('<a onmouseover=alert(document.cookie)>xxs link</a>', '<a>xxs link</a>');

    // Malformed IMG tags.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Malformed_IMG_tags
    $data[] = array('<IMG """><SCRIPT>alert("XSS")</SCRIPT>">', '<IMG>alert("XSS")"&gt;');

    // fromCharCode.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#fromCharCode
    $data[] = array('<IMG SRC=javascript:alert(String.fromCharCode(88,83,83))>', '<IMG src="alert(String.fromCharCode(88,83,83))">');

    // Default SRC tag to get past filters that check SRC domain.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Default_SRC_tag_to_get_past_filters_that_check_SRC_domain
    $data[] = array('<IMG SRC=# onmouseover="alert(\'xxs\')">', '<IMG src="#">');

    // Default SRC tag by leaving it empty.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Default_SRC_tag_by_leaving_it_empty
    $data[] = array('<IMG SRC= onmouseover="alert(\'xxs\')">', '<IMG nmouseover="alert(&#039;xxs&#039;)">');

    // Default SRC tag by leaving it out entirely.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Default_SRC_tag_by_leaving_it_out_entirely
    $data[] = array('<IMG onmouseover="alert(\'xxs\')">', '<IMG>');

    // Decimal HTML character references.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Decimal_HTML_character_references
    $data[] = array('<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>', '<IMG src="alert(&#039;XSS&#039;)">');

    // Decimal HTML character references without trailing semicolons.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Decimal_HTML_character_references_without_trailing_semicolons
    $data[] = array('<IMG SRC=&#0000106&#0000097&#0000118&#0000097&#0000115&#0000099&#0000114&#0000105&#0000112&#0000116&#0000058&#0000097&#0000108&#0000101&#0000114&#0000116&#0000040&#0000039&#0000088&#0000083&#0000083&#0000039&#0000041>', '<IMG src="&amp;#0000106&amp;#0000097&amp;#0000118&amp;#0000097&amp;#0000115&amp;#0000099&amp;#0000114&amp;#0000105&amp;#0000112&amp;#0000116&amp;#0000058&amp;#0000097&amp;#0000108&amp;#0000101&amp;#0000114&amp;#0000116&amp;#0000040&amp;#0000039&amp;#0000088&amp;#0000083&amp;#0000083&amp;#0000039&amp;#0000041">');

    // Hexadecimal HTML character references without trailing semicolons.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Hexadecimal_HTML_character_references_without_trailing_semicolons
    $data[] = array('<IMG SRC=&#x6A&#x61&#x76&#x61&#x73&#x63&#x72&#x69&#x70&#x74&#x3A&#x61&#x6C&#x65&#x72&#x74&#x28&#x27&#x58&#x53&#x53&#x27&#x29>', '<IMG src="&amp;#x6A&amp;#x61&amp;#x76&amp;#x61&amp;#x73&amp;#x63&amp;#x72&amp;#x69&amp;#x70&amp;#x74&amp;#x3A&amp;#x61&amp;#x6C&amp;#x65&amp;#x72&amp;#x74&amp;#x28&amp;#x27&amp;#x58&amp;#x53&amp;#x53&amp;#x27&amp;#x29">');

    // Embedded tab.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_tab
    $data[] = array('<IMG SRC="jav  ascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // Embedded Encoded tab.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_Encoded_tab
    $data[] = array('<IMG SRC="jav&#x09;ascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // Embedded newline to break up XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_newline_to_break_up_XSS
    $data[] = array('<IMG SRC="jav&#x0A;ascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // Embedded carriage return to break up XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Embedded_carriage_return_to_break_up_XSS
    $data[] = array('<IMG SRC="jav&#x0D;ascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // Null breaks up JavaScript directive.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Null_breaks_up_JavaScript_directive
    $data[] = array("<IMG SRC=java\0script:alert(\"XSS\")>", '<IMG>');

    // Spaces and meta chars before the JavaScript in images for XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Spaces_and_meta_chars_before_the_JavaScript_in_images_for_XSS
    $data[] = array('<IMG SRC=" &#14;  javascript:alert(\'XSS\');">', '<IMG src="alert(&#039;XSS&#039;);">');

    // Non-alpha-non-digit XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Non-alpha-non-digit_XSS
    $data[] = array('<SCRIPT/XSS SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '');
    $data[] = array('<BODY onload!#$%&()*~+-_.,:;?@[/|\]^`=alert("XSS")>', '<BODY>');
    $data[] = array('<SCRIPT/SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '');

    // Extraneous open brackets.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Extraneous_open_brackets
    $data[] = array('<<SCRIPT>alert("XSS");//<</SCRIPT>', '&lt;alert("XSS");//&lt;');

    // No closing script tags.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#No_closing_script_tags
    $data[] = array('<SCRIPT SRC=http://ha.ckers.org/xss.js?< B >', '');

    // Protocol resolution in script tags.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Protocol_resolution_in_script_tags
    $data[] = array('<SCRIPT SRC=//ha.ckers.org/.j>', '');

    // Half open HTML/JavaScript XSS vector.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Half_open_HTML.2FJavaScript_XSS_vector
    $data[] = array('<IMG SRC="javascript:alert(\'XSS\')"', '<IMG src="alert(&#039;XSS&#039;)">');

    // Double open angle brackets.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Double_open_angle_brackets
    // @see http://ha.ckers.org/blog/20060611/hotbot-xss-vulnerability/ to
    //      understand why this is a vulnerability.
    $data[] = array('<iframe src=http://ha.ckers.org/scriptlet.html <', '<iframe src="http://ha.ckers.org/scriptlet.html">');

    // Escaping JavaScript escapes.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Escaping_JavaScript_escapes
    // This one is irrelevent for Drupal; we *never* output any JavaScript code
    // that depends on the URL's query string.

    // End title tag.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#End_title_tag
    $data[] = array('</TITLE><SCRIPT>alert("XSS");</SCRIPT>', '</TITLE>alert("XSS");');

    // INPUT image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#INPUT_image
    $data[] = array('<INPUT TYPE="IMAGE" SRC="javascript:alert(\'XSS\');">', '<INPUT type="IMAGE" src="alert(&#039;XSS&#039;);">');

    // BODY image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#BODY_image
    $data[] = array('<BODY BACKGROUND="javascript:alert(\'XSS\')">', '<BODY background="alert(&#039;XSS&#039;)">');

    // IMG Dynsrc.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IMG_Dynsrc
    $data[] = array('<IMG DYNSRC="javascript:alert(\'XSS\')">', '<IMG dynsrc="alert(&#039;XSS&#039;)">');

    // IMG lowrsc.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IMG_lowsrc
    $data[] = array('<IMG LOWSRC="javascript:alert(\'XSS\')">', '<IMG lowsrc="alert(&#039;XSS&#039;)">');

    // List-style-image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#List-style-image
    $data[] = array('<STYLE>li {list-style-image: url("javascript:alert(\'XSS\')");}</STYLE><UL><LI>XSS</br>', 'li {list-style-image: url("javascript:alert(\'XSS\')");}<UL><LI>XSS</br>');

    // VBscript in an image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#VBscript_in_an_image
    $data[] = array('<IMG SRC=\'vbscript:msgbox("XSS")\'>', '<IMG src=\'msgbox(&quot;XSS&quot;)\'>');

    // Livescript (older versions of Netscape only).
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Livescript_.28older_versions_of_Netscape_only.29
    $data[] = array('<IMG SRC="livescript:[code]">', '<IMG src="[code]">');

    // BODY tag.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#BODY_tag
    $data[] = array('<BODY ONLOAD=alert(\'XSS\')>', '<BODY>');

    // Event handlers.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Event_Handlers
    $events = array(
      'onAbort',
      'onActivate',
      'onAfterPrint',
      'onAfterUpdate',
      'onBeforeActivate',
      'onBeforeCopy',
      'onBeforeCut',
      'onBeforeDeactivate',
      'onBeforeEditFocus',
      'onBeforePaste',
      'onBeforePrint',
      'onBeforeUnload',
      'onBeforeUpdate',
      'onBegin',
      'onBlur',
      'onBounce',
      'onCellChange',
      'onChange',
      'onClick',
      'onContextMenu',
      'onControlSelect',
      'onCopy',
      'onCut',
      'onDataAvailable',
      'onDataSetChanged',
      'onDataSetComplete',
      'onDblClick',
      'onDeactivate',
      'onDrag',
      'onDragEnd',
      'onDragLeave',
      'onDragEnter',
      'onDragOver',
      'onDragDrop',
      'onDragStart',
      'onDrop',
      'onEnd',
      'onError',
      'onErrorUpdate',
      'onFilterChange',
      'onFinish',
      'onFocus',
      'onFocusIn',
      'onFocusOut',
      'onHashChange',
      'onHelp',
      'onInput',
      'onKeyDown',
      'onKeyPress',
      'onKeyUp',
      'onLayoutComplete',
      'onLoad',
      'onLoseCapture',
      'onMediaComplete',
      'onMediaError',
      'onMessage',
      'onMousedown',
      'onMouseEnter',
      'onMouseLeave',
      'onMouseMove',
      'onMouseOut',
      'onMouseOver',
      'onMouseUp',
      'onMouseWheel',
      'onMove',
      'onMoveEnd',
      'onMoveStart',
      'onOffline',
      'onOnline',
      'onOutOfSync',
      'onPaste',
      'onPause',
      'onPopState',
      'onProgress',
      'onPropertyChange',
      'onReadyStateChange',
      'onRedo',
      'onRepeat',
      'onReset',
      'onResize',
      'onResizeEnd',
      'onResizeStart',
      'onResume',
      'onReverse',
      'onRowsEnter',
      'onRowExit',
      'onRowDelete',
      'onRowInserted',
      'onScroll',
      'onSeek',
      'onSelect',
      'onSelectionChange',
      'onSelectStart',
      'onStart',
      'onStop',
      'onStorage',
      'onSyncRestored',
      'onSubmit',
      'onTimeError',
      'onTrackChange',
      'onUndo',
      'onUnload',
      'onURLFlip',
    );
    foreach ($events as $event) {
      $data[] = array('<p ' . $event . '="javascript:alert(\'XSS\');">Dangerous llama!</p>', '<p>Dangerous llama!</p>');
    }

    // BGSOUND.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#BGSOUND
    $data[] = array('<BGSOUND SRC="javascript:alert(\'XSS\');">', '<BGSOUND src="alert(&#039;XSS&#039;);">');

    // & JavaScript includes.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#.26_JavaScript_includes
    $data[] = array('<BR SIZE="&{alert(\'XSS\')}">', '<BR size="">');

    // STYLE sheet.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_sheet
    $data[] = array('<LINK REL="stylesheet" HREF="javascript:alert(\'XSS\');">', '');

    // Remote style sheet.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Remote_style_sheet
    $data[] = array('<LINK REL="stylesheet" HREF="http://ha.ckers.org/xss.css">', '');

    // Remote style sheet part 2.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Remote_style_sheet_part_2
    $data[] = array('<STYLE>@import\'http://ha.ckers.org/xss.css\';</STYLE>', '@import\'http://ha.ckers.org/xss.css\';');

    // Remote style sheet part 3.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Remote_style_sheet_part_3
    $data[] = array('<META HTTP-EQUIV="Link" Content="<http://ha.ckers.org/xss.css>; REL=stylesheet">', '<META http-equiv="Link">; REL=stylesheet"&gt;');

    // Remote style sheet part 4.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Remote_style_sheet_part_4
    $data[] = array('<STYLE>BODY{-moz-binding:url("http://ha.ckers.org/xssmoz.xml#xss")}</STYLE>', 'BODY{-moz-binding:url("http://ha.ckers.org/xssmoz.xml#xss")}');

    // STYLE tags with broken up JavaScript for XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_tags_with_broken_up_JavaScript_for_XSS
    $data[] = array('<STYLE>@im\port\'\ja\vasc\ript:alert("XSS")\';</STYLE>', '@im\port\'\ja\vasc\ript:alert("XSS")\';');

    // STYLE attribute using a comment to break up expression.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_attribute_using_a_comment_to_break_up_expression
    $data[] = array('<IMG STYLE="xss:expr/*XSS*/ession(alert(\'XSS\'))">', '<IMG>');

    // IMG STYLE with expression.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IMG_STYLE_with_expression
    $data[] = array('exp/*<A STYLE=\'no\xss:noxss("*//*");
xss:ex/*XSS*//*/*/pression(alert("XSS"))\'>', 'exp/*<A>');

    // STYLE tag (Older versions of Netscape only).
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_tag_.28Older_versions_of_Netscape_only.29
    $data[] = array('<STYLE TYPE="text/javascript">alert(\'XSS\');</STYLE>', 'alert(\'XSS\');');

    // STYLE tag using background-image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_tag_using_background-image
    $data[] = array('<STYLE>.XSS{background-image:url("javascript:alert(\'XSS\')");}</STYLE><A CLASS=XSS></A>', '.XSS{background-image:url("javascript:alert(\'XSS\')");}<A class="XSS"></A>');

    // STYLE tag using background.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#STYLE_tag_using_background
    $data[] = array('<STYLE type="text/css">BODY{background:url("javascript:alert(\'XSS\')")}</STYLE>', 'BODY{background:url("javascript:alert(\'XSS\')")}');

    // Anonymous HTML with STYLE attribute.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Anonymous_HTML_with_STYLE_attribute
    $data[] = array('<XSS STYLE="xss:expression(alert(\'XSS\'))">', '<XSS>');

    // Local htc file.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Local_htc_file
    $data[] = array('<XSS STYLE="behavior: url(xss.htc);">', '<XSS>');

    // US-ASCII encoding.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#US-ASCII_encoding
    // This one is irrelevant for Drupal; Drupal *always* outputs UTF-8.

    // META.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
    $data[] = array('<META HTTP-EQUIV="refresh" CONTENT="0;url=javascript:alert(\'XSS\');">', '<META http-equiv="refresh" content="alert(&#039;XSS&#039;);">');

    // META using data.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META_using_data
    $data[] = array('<META HTTP-EQUIV="refresh" CONTENT="0;url=data:text/html base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4K">', '<META http-equiv="refresh" content="text/html base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4K">');

    // META with additional URL parameter
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#META
    $data[] = array('<META HTTP-EQUIV="refresh" CONTENT="0; URL=http://;URL=javascript:alert(\'XSS\');">', '<META http-equiv="refresh" content="//;URL=javascript:alert(&#039;XSS&#039;);">');

    // IFRAME.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IFRAME
    $data[] = array('<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>', '<IFRAME src="alert(&#039;XSS&#039;);"></IFRAME>');

    // IFRAME Event based.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IFRAME_Event_based
    $data[] = array('<IFRAME SRC=# onmouseover="alert(document.cookie)"></IFRAME>', '<IFRAME src="#"></IFRAME>');

    // FRAME.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#FRAME
    $data[] = array('<FRAMESET><FRAME SRC="javascript:alert(\'XSS\');"></FRAMESET>', '<FRAMESET><FRAME src="alert(&#039;XSS&#039;);"></FRAMESET>');

    // TABLE.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#TABLE
    $data[] = array('<TABLE BACKGROUND="javascript:alert(\'XSS\')">', '<TABLE background="alert(&#039;XSS&#039;)">');

    // TD.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#TD
    $data[] = array('<TABLE><TD BACKGROUND="javascript:alert(\'XSS\')">', '<TABLE><TD background="alert(&#039;XSS&#039;)">');

    // DIV background-image.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#DIV_background-image
    $data[] = array('<DIV STYLE="background-image: url(javascript:alert(\'XSS\'))">', '<DIV>');

    // DIV background-image with unicoded XSS exploit.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#DIV_background-image_with_unicoded_XSS_exploit
    $data[] = array('<DIV STYLE="background-image:\0075\0072\006C\0028\'\006a\0061\0076\0061\0073\0063\0072\0069\0070\0074\003a\0061\006c\0065\0072\0074\0028.1027\0058.1053\0053\0027\0029\'\0029">', '<DIV>');

    // DIV background-image plus extra characters.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#DIV_background-image_plus_extra_characters
    $data[] = array('<DIV STYLE="background-image: url(&#1;javascript:alert(\'XSS\'))">', '<DIV>');

    // DIV expression.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#DIV_expression
    $data[] = array('<DIV STYLE="width: expression(alert(\'XSS\'));">', '<DIV>');

    // Downlevel-Hidden block.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Downlevel-Hidden_block
    $data[] = array('<!--[if gte IE 4]>
 <SCRIPT>alert(\'XSS\');</SCRIPT>
 <![endif]-->', "\n alert('XSS');\n ");

    // BASE tag.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#BASE_tag
    $data[] = array('<BASE HREF="javascript:alert(\'XSS\');//">', '<BASE href="alert(&#039;XSS&#039;);//">');

    // OBJECT tag.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#OBJECT_tag
    $data[] = array('<OBJECT TYPE="text/x-scriptlet" DATA="http://ha.ckers.org/scriptlet.html"></OBJECT>', '');

    // Using an EMBED tag you can embed a Flash movie that contains XSS.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Using_an_EMBED_tag_you_can_embed_a_Flash_movie_that_contains_XSS
    $data[] = array('<EMBED SRC="http://ha.ckers.org/xss.swf" AllowScriptAccess="always"></EMBED>', '');

    // You can EMBED SVG which can contain your XSS vector.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#You_can_EMBED_SVG_which_can_contain_your_XSS_vector
    $data[] = array('<EMBED SRC="data:image/svg+xml;base64,PHN2ZyB4bWxuczpzdmc9Imh0dH A6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcv MjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hs aW5rIiB2ZXJzaW9uPSIxLjAiIHg9IjAiIHk9IjAiIHdpZHRoPSIxOTQiIGhlaWdodD0iMjAw IiBpZD0ieHNzIj48c2NyaXB0IHR5cGU9InRleHQvZWNtYXNjcmlwdCI+YWxlcnQoIlh TUyIpOzwvc2NyaXB0Pjwvc3ZnPg==" type="image/svg+xml" AllowScriptAccess="always"></EMBED>', '');

    // XML data island with CDATA obfuscation.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#XML_data_island_with_CDATA_obfuscation
    $data[] = array('<XML ID="xss"><I><B><IMG SRC="javas<!-- -->cript:alert(\'XSS\')"></B></I></XML><SPAN DATASRC="#xss" DATAFLD="B" DATAFORMATAS="HTML"></SPAN>', '<XML id="xss"><I><B><IMG>cript:alert(\'XSS\')"&gt;</B></I></XML><SPAN datasrc="#xss" datafld="B" dataformatas="HTML"></SPAN>');

    // Locally hosted XML with embedded JavaScript that is generated using an XML data island.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Locally_hosted_XML_with_embedded_JavaScript_that_is_generated_using_an_XML_data_island
    // This one is irrelevant for Drupal; Drupal disallows XML uploads by
    // default.

    // HTML+TIME in XML.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#HTML.2BTIME_in_XML
    $data[] = array('<?xml:namespace prefix="t" ns="urn:schemas-microsoft-com:time"><?import namespace="t" implementation="#default#time2"><t:set attributeName="innerHTML" to="XSS<SCRIPT DEFER>alert("XSS")</SCRIPT>">', '&lt;?xml:namespace prefix="t" ns="urn:schemas-microsoft-com:time"&gt;&lt;?import namespace="t" implementation="#default#time2"&gt;<t set attributename="innerHTML">alert("XSS")"&gt;');

    // Assuming you can only fit in a few characters and it filters against ".js".
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Assuming_you_can_only_fit_in_a_few_characters_and_it_filters_against_.22.js.22
    $data[] = array('<SCRIPT SRC="http://ha.ckers.org/xss.jpg"></SCRIPT>', '');

    // IMG Embedded commands.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#IMG_Embedded_commands
    // This one is irrelevant for Drupal; this is actually a CSRF, for which
    // Drupal has CSRF protection, see https://drupal.org/node/178896.

    // Cookie manipulation.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#Cookie_manipulation
    $data[] = array('<META HTTP-EQUIV="Set-Cookie" Content="USERID=<SCRIPT>alert(\'XSS\')</SCRIPT>">', '<META http-equiv="Set-Cookie">alert(\'XSS\')"&gt;');

    // UTF-7 encoding.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#UTF-7_encoding
    // This one is irrelevant for Drupal; Drupal *always* outputs UTF-8.

    // XSS using HTML quote encapsulation.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#XSS_using_HTML_quote_encapsulation
    $data[] = array('<SCRIPT a=">" SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '" SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT =">" SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '" SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT a=">" \'\' SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '" \'\' SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT "a=\'>\'" SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '\'" SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT a=`>` SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '` SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT a=">\'>" SRC="http://ha.ckers.org/xss.js"></SCRIPT>', '\'&gt;" SRC="http://ha.ckers.org/xss.js"&gt;');
    $data[] = array('<SCRIPT>document.write("<SCRI");</SCRIPT>PT SRC="http://ha.ckers.org/xss.js"></SCRIPT>', 'document.write("<SCRI>PT SRC="http://ha.ckers.org/xss.js"&gt;');

    // URL string evasion.
    // @see https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet#URL_string_evasion
    // This one is irrelevant for Drupal; Drupal doesn't forbid linking to some
    // sites, it only forbids linking to any protocols other than those that are
    // whitelisted.

    return $data;
  }

  /**
   * Tests the method for checking access to routes.
   *
   * @param string $input
   *   The input.
   * @param string $expected_output
   *   The expected output.
   *
   * @dataProvider providerTestFilterXss
   */
  public function testFilterXss($input, $expected_output) {
    $output = call_user_func($this->editorXssFilterClass . '::filterXss', $input, $this->format);
    $this->assertSame($expected_output, $output);
  }

}
