<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Menu\MenuLinkTreeTest.
 */

namespace Drupal\Tests\Core\Menu;

use Drupal\Component\Plugin\Factory\FactoryInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Tests\UnitTestCase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests the menu link tree.
 *
 * @group Drupal
 * @group Menu
 */
class MenuLinkTreeTest extends UnitTestCase {

  /**
   * The tested menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTree
   */
  protected $menuTree;

  /**
   * @var \Drupal\Core\Menu\MenuLinkTreeStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $treeStorage;

  protected $treeItemDefault = array(
    'definition' => array(),
    'has_children' => 0,
    'in_active_trail' => TRUE,
    'below' => array(),
    'depth' => 1,
  );

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Tests \Drupal\Core\Menu\MenuLinkTree',
      'description' => '',
      'group' => 'Menu'
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->treeStorage = $this->getMock('Drupal\Core\Menu\MenuLinkTreeStorageInterface');
    $this->staticOverride = $this->getMock('Drupal\Core\Menu\StaticMenuLinkOverridesInterface');
    $this->requestStack = new RequestStack();
    $this->routeProvider = $this->getMock('Drupal\Core\Routing\RouteProviderInterface');
    $this->moduleHandler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $this->treeCacheBackend = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');
    $this->languageManager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $this->accessManager = $this->getMockBuilder('Drupal\Core\Access\AccessManager')
      ->disableOriginalConstructor()
      ->getMock();
    $this->account = $this->getMock('Drupal\Core\Session\AccountInterface');
    $this->entityManager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $this->factory = $this->getMock('Drupal\Component\Plugin\Factory\FactoryInterface');
    $this->menuTree = new TestMenuLinkTree($this->treeStorage, $this->staticOverride, $this->requestStack, $this->routeProvider, $this->moduleHandler, $this->treeCacheBackend, $this->languageManager, $this->accessManager, $this->account, $this->entityManager);
    $this->stringTranslation = $this->getStringTranslationStub();
    $container = new ContainerBuilder();
    $container->set('string_translation', $this->stringTranslation);
    \Drupal::setContainer($container);
    $this->menuTree->setFactory($this->factory);
  }

  public function testMenuLinkGetPreferred() {
    $this->menuLinkGetPreferredTreeStorageHelper();

    $result = $this->menuTree->menuLinkGetPreferred('test_route');
    $this->assertEquals('test1', $result->getPluginId());
  }

  public function testMenuLinkGetPreferredWithSpecifiedMenu() {
    $this->menuLinkGetPreferredTreeStorageHelper();

    $result = $this->menuTree->menuLinkGetPreferred('test_route', array(), 'tools');
    $this->assertEquals('test2', $result->getPluginId());
  }

  public function testMenuLinkGetPreferredStaticCaching() {
    // the helper uses $this->once() so we test the static caching with that.
    $this->menuLinkGetPreferredTreeStorageHelper();

    $result = $this->menuTree->menuLinkGetPreferred('test_route', array());
    $this->assertEquals('test1', $result->getPluginId());
    $result = $this->menuTree->menuLinkGetPreferred('test_route', array());
    $this->assertEquals('test1', $result->getPluginId());
  }

  public function testMenuLinkGetPreferredStaticCachingWithSelectedMenu() {
    $this->menuLinkGetPreferredTreeStorageHelper();

    $result = $this->menuTree->menuLinkGetPreferred('test_route', array());
    $this->assertEquals('test1', $result->getPluginId());

    $result = $this->menuTree->menuLinkGetPreferred('test_route', array(), 'tools');
    $this->assertEquals('test2', $result->getPluginId());
  }

  public function testMenuLinkGetPreferredWithNoMatchingMenuLink() {
    $this->treeStorage->expects($this->once())
      ->method('loadByRoute')
      ->with('test_route')
      ->will($this->returnValue(array()));
    $this->accessManager->expects($this->any())
      ->method('checkNamedRoute')
      ->will($this->returnValue(TRUE));

    $result = $this->menuTree->menuLinkGetPreferred('test_route', array(), 'tools');
    $this->assertNull($result);
  }

  public function testMenuLinkGetPreferredWithAccessDenied() {
    $this->accessManager->expects($this->any())
      ->method('checkNamedRoute')
      ->will($this->returnValue(FALSE));
    $result = $this->menuTree->menuLinkGetPreferred('test_route', array());
    $this->assertNull($result);
  }

  public function testMenuLinkGetPreferredForCurrentRequest() {
    $request = new Request();
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'test_route');
    $raw_variables = new ParameterBag();
    $request->attributes->set('_raw_variables', $raw_variables);
    $this->requestStack->push($request);

  }

  protected function menuLinkGetPreferredTreeStorageHelper() {
    $definitions = array(
      'test1' => array(
        'id' => 'test1',
        'menu_name' => 'admin',
        'class' => 'Drupal\Core\Menu\MenuLinkDefault',
      ),
      'test2' => array(
        'id' => 'test2',
        'menu_name' => 'tools',
        'class' => 'Drupal\Core\Menu\MenuLinkDefault',
      ),
    );
    $this->treeStorage->expects($this->once())
      ->method('loadByRoute')
      ->with('test_route')
      ->will($this->returnValue($definitions));

    $this->accessManager->expects($this->any())
      ->method('checkNamedRoute')
      ->will($this->returnValue(TRUE));
    $this->factory->expects($this->any())
      ->method('createInstance')
      ->will($this->returnCallback(function ($plugin_id) use ($definitions) {
        return new MenuLinkDefault(array(), $plugin_id, $definitions[$plugin_id], $this->staticOverride);
      }));
  }

    /**
   * Tests the output with a single level.
   *
   * @covers ::renderTree
   */
  public function testOutputWithSingleLevel() {
    $tree = array(
      'test1' => array(
        'link' => $this->menuLinkInstanceHelper('test1'),
      ) + $this->treeItemDefault,
      'test2' => array(
        'link' => $this->menuLinkInstanceHelper('test2'),
      ) + $this->treeItemDefault,
    );

    $output = $this->menuTree->renderTree($tree);

    // Validate that the - in main-menu is changed into an underscore
    print_r($output);
    $this->assertEquals($output['test1']['#theme'], 'menu_link__tools', 'Hyphen is changed to an underscore on menu_link');
    $this->assertEquals($output['test2']['#theme'], 'menu_link__tools', 'Hyphen is changed to an underscore on menu_link');
    $this->assertEquals($output['#theme_wrappers'][0], 'menu_tree__tools', 'Hyphen is changed to an underscore on menu_tree wrapper');
  }

  /**
   * Tests the output method with a complex example.
   *
   * @covers ::renderTree
   */
  public function testOutputWithComplexData() {
    $tree = array(
      'test1'=> array(
        'link' => $this->menuLinkInstanceHelper('test1', 'Item 1', 'test_a'),
        'below' => array(
          'test2' => array('link' => $this->menuLinkInstanceHelper('test2', 'Item 2', 'test_a_b'),
            'below' => array(
              'test3' => array('link' => $this->menuLinkInstanceHelper('test3', 'Item 3', 'test_a_b_c'),
              ) + $this->treeItemDefault,
              'test4' => array('link' => $this->menuLinkInstanceHelper('test4', 'Item 4', 'test_a_b_d'),
              ) + $this->treeItemDefault,
            )
          ) + $this->treeItemDefault,
        ),
      ) + $this->treeItemDefault,
      'test5' => array('link' => $this->menuLinkInstanceHelper('test5', 'Item 5', 'test_e')) + $this->treeItemDefault,
    );

    $output = $this->menuTree->renderTree($tree);

    // Looking for child items in the data
    $this->assertEquals($output['test1']['#below']['test2']['#url']->getRouteName(), 'test_a_b', 'Checking the href on a child item');
    $this->assertTrue(in_array('active-trail', $output['test1']['#below']['test2']['#attributes']['class']), 'Checking the active trail class');
    $this->assertTrue(isset($output['test5']), 'Item is present');
  }

  protected function menuLinkInstanceHelper($id, $title = '', $route_name = '', $extra = array()) {
    $defaults = array(
      'menu_name' => 'tools',
      'route_name' => '<front>',
      'route_parameters' => array(),
      'url' => '',
      'title' => '',
      'title_arguments' => array(),
      'title_context' => '',
      'description' => '',
      'parent' => '',
      'weight' => 0,
      'options' => array(),
      'expanded' => 0,
      'hidden' => 0,
      'discovered' => 0,
      'provider' => '',
      'metadata' => array(),
      'class' => 'Drupal\Core\Menu\MenuLinkDefault',
      'form_class' => 'Drupal\Core\Menu\Form\MenuLinkDefaultForm',
      'id' => '',
    );
    $defaults['title'] = $title;
    $defaults['route_name'] = $route_name;
    $defaults['id'] = $id;
    $extra += $defaults;
    return new MenuLinkDefault(array(), $defaults['id'], $extra, $this->staticOverride);
  }

}

class TestMenuLinkTree extends MenuLinkTree {

  protected function menuGetActiveMenuNames() {
    return array();
  }

  public function setFactory(FactoryInterface $factory) {
    $this->factory = $factory;
  }

}
