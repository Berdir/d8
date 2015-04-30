<?php

/**
 * @file
 * Contains \Drupal\Tests\block\Unit\BlockRepositoryTest.
 */

namespace Drupal\Tests\block\Unit;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\block\BlockRepository
 * @group block
 */
class BlockRepositoryTest extends UnitTestCase {

  /**
   * @var \Drupal\block\BlockRepository
   */
  protected $blockRepository;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $blockStorage;

  /**
   * @var string
   */
  protected $theme;

  /**
   * @var \Drupal\Core\Plugin\Context\ContextHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $contextHandler;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cacheBackend;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $active_theme = $this->getMockBuilder('Drupal\Core\Theme\ActiveTheme')
      ->disableOriginalConstructor()
      ->getMock();
    $this->theme = $this->randomMachineName();
    $active_theme->expects($this->atLeastOnce())
      ->method('getName')
      ->willReturn($this->theme);

    $theme_manager = $this->getMock('Drupal\Core\Theme\ThemeManagerInterface');
    $theme_manager->expects($this->once())
      ->method('getActiveTheme')
      ->will($this->returnValue($active_theme));

    $this->contextHandler = $this->getMock('Drupal\Core\Plugin\Context\ContextHandlerInterface');
    $this->blockStorage = $this->getMock('Drupal\Core\Entity\EntityStorageInterface');
    $entity_manager = $this->getMock('Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->blockStorage);
    $this->cacheBackend = $this->getMock('Drupal\Core\Cache\CacheBackendInterface');

    $this->blockRepository = $this->getMockBuilder('Drupal\block\BlockRepository')
      ->setConstructorArgs([$entity_manager, $theme_manager, $this->contextHandler, $this->cacheBackend])
      ->setMethods(['getRegionNames'])
      ->getMock();
    $this->blockRepository->expects($this->once())
      ->method('getRegionNames')
      ->willReturn([
        'top' => 'Top',
        'center' => 'Center',
        'bottom' => 'Bottom',
      ]);
  }

  /**
   * Tests the retrieval of block entities.
   *
   * @covers ::getVisibleBlocksPerRegion
   *
   * @dataProvider providerBlocksConfig
   */
  public function testGetVisibleBlocksPerRegion(array $blocks_config, array $expected_blocks) {
    $blocks = [];
    foreach ($blocks_config as $block_id => $block_config) {
      $block = $this->getMock('Drupal\block\BlockInterface');
      $block->expects($this->once())
        ->method('setContexts')
        ->willReturnSelf();
      $block->expects($this->once())
        ->method('access')
        ->will($this->returnValue($block_config[0]));
      $block->expects($block_config[0] ? $this->atLeastOnce() : $this->never())
        ->method('getRegion')
        ->willReturn($block_config[1]);
      $blocks[$block_id] = $block;
    }

    $this->blockStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['theme' => $this->theme])
      ->willReturn($blocks);

    $result = $this->getAllVisibleBlocks([]);
    $this->assertSame($result, $expected_blocks);
  }

  public function providerBlocksConfig() {
    $blocks_config = array(
      'block1' => array(
        TRUE, 'top', 0
      ),
      // Test a block without access.
      'block2' => array(
        FALSE, 'bottom', 0
      ),
      // Test two blocks in the same region with specific weight.
      'block3' => array(
        TRUE, 'bottom', 5
      ),
      'block4' => array(
        TRUE, 'bottom', -5
      ),
    );

    $test_cases = [];
    $test_cases[] = [$blocks_config,
      [
        'top' => ['block1'],
        'center' => [],
        'bottom' => ['block4', 'block3'],
      ]
    ];
    return $test_cases;
  }

  /**
   * Tests the retrieval of block entities that are context-aware.
   *
   * @covers ::getVisibleBlocksPerRegion
   */
  public function testGetVisibleBlocksPerRegionWithContext() {
    $contexts = [];
    $contexts[] = new Context(new ContextDefinition('entity:user', 'Current user'));

    $block = $this->getMock('Drupal\block\BlockInterface');
    $block->expects($this->once())
      ->method('setContexts')
      ->with($contexts)
      ->willReturnSelf();
    $block->expects($this->once())
      ->method('access')
      ->willReturn(TRUE);
    $block->expects($this->once())
      ->method('getRegion')
      ->willReturn('top');
    $blocks['block_id'] = $block;

    $this->blockStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['theme' => $this->theme])
      ->willReturn($blocks);

    $result = $this->getAllVisibleBlocks($contexts);
    $expected = [
      'top' => [
        'block_id',
      ],
      'center' => [],
      'bottom' => [],
    ];
    $this->assertSame($expected, $result);
  }

  /**
   * @covers ::getVisibleBlocksPerRegion
   */
  public function testGetVisibleBlocksPerRegionFromCache() {
    $this->blockStorage->expects($this->never())
      ->method('loadByProperties');

    $block = $this->getMock('Drupal\block\BlockInterface');
    $block->expects($this->once())
      ->method('setContexts')
      ->willReturnSelf();
    $block->expects($this->once())
      ->method('access')
      ->willReturn(TRUE);
    $block->expects($this->once())
      ->method('getRegion')
      ->willReturn('top');
    $blocks['block_id'] = $block;

    $this->cacheBackend->expects($this->once())
      ->method('get')
      ->with('block_list:' . $this->theme)
      ->willReturn((object) ['data' => $blocks]);

    $result = $this->getAllVisibleBlocks([]);
    $expected = [
      'top' => [
        'block_id',
      ],
      'center' => [],
      'bottom' => [],
    ];
    $this->assertSame($expected, $result);
  }

  /**
   * Calls getVisibleBlocksPerRegion() for a set of contexts.
   */
  protected function getAllVisibleBlocks(array $contexts) {
    $result = [];
    foreach ($this->blockRepository->getVisibleBlocksPerRegion($contexts) as $region => $resulting_blocks) {
      $result[$region] = [];
      foreach ($resulting_blocks as $plugin_id => $block) {
        $result[$region][] = $plugin_id;
      }
    }
    return $result;
  }

}

interface TestContextAwareBlockInterface extends BlockPluginInterface, ContextAwarePluginInterface {
}
