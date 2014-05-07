<?php
/**
 * @file
 * Contains \Drupal\Tests\Core\Entity\Sql\DefaultTableMappingTest.
 */

namespace Drupal\Tests\Core\Entity\Sql;
use Drupal\Core\Entity\Sql\DefaultTableMapping;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the default table mapping class.
 *
 * @coversDefaultClass \Drupal\Core\Entity\Sql\DefaultTableMapping
 *
 * @group Drupal
 * @group Entity
 */
class DefaultTableMappingTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Default table mapping',
      'description' => 'Check that the default table mapping works.',
      'group' => 'Entity',
    ];
  }

  /**
   * Tests DefaultTableMapping::getTableNames().
   *
   * @covers ::getTableNames
   */
  public function testGetTableNames() {
    // The storage definitions are only used in getColumnMapping() so we do not
    // need to provide any here.
    $table_mapping = new DefaultTableMapping([]);
    $this->assertSame([], $table_mapping->getTableNames());

    $table_mapping->addFieldColumns('foo', []);
    $this->assertSame(['foo'], $table_mapping->getTableNames());

    $table_mapping->addFieldColumns('bar', []);
    $this->assertSame(['foo', 'bar'], $table_mapping->getTableNames());

    $table_mapping->addExtraColumns('baz', []);
    $this->assertSame(['foo', 'bar', 'baz'], $table_mapping->getTableNames());

    // Test that table names are not duplicated.
    $table_mapping->addExtraColumns('foo', []);
    $this->assertSame(['foo', 'bar', 'baz'], $table_mapping->getTableNames());
  }

  /**
   * Tests DefaultTableMapping::getAllColumns().
   *
   * @covers ::__construct()
   * @covers ::getAllColumns()
   * @covers ::getFieldNames()
   * @covers ::getColumnMapping()
   * @covers ::addFieldColumns()
   * @covers ::getExtraColumns()
   * @covers ::addExtraColumns()
   */
  public function testGetAllColumns() {
    // Set up single-column and multi-column definitions.
    $definitions['id'] = $this->setUpDefinition(['value']);
    $definitions['name'] = $this->setUpDefinition(['value']);
    $definitions['type'] = $this->setUpDefinition(['value']);
    $definitions['description'] = $this->setUpDefinition(['value', 'format']);
    $definitions['owner'] = $this->setUpDefinition([
      'target_id',
      'target_revision_id',
    ]);

    $table_mapping = new DefaultTableMapping($definitions);
    $expected = [];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    // Test adding field columns.
    $table_mapping->addFieldColumns('test', ['id']);
    $expected = ['id'];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    $table_mapping->addFieldColumns('test', ['id', 'name']);
    $expected = ['id', 'name'];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    $table_mapping->addFieldColumns('test', ['id', 'name', 'type']);
    $expected = ['id', 'name', 'type'];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    $table_mapping->addFieldColumns('test', [
      'id',
      'name',
      'type',
      'description',
    ]);
    $expected = [
      'id',
      'name',
      'type',
      'description__value',
      'description__format',
    ];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    $table_mapping->addFieldColumns('test', [
      'id',
      'name',
      'type',
      'description',
      'owner',
    ]);
    $expected = [
      'id',
      'name',
      'type',
      'description__value',
      'description__format',
      'owner__target_id',
      'owner__target_revision_id',
    ];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    // Test adding extra columns.
    $table_mapping->addFieldColumns('test', []);
    $table_mapping->addExtraColumns('test', ['default_langcode']);
    $expected = ['default_langcode'];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    $table_mapping->addExtraColumns('test', [
      'default_langcode',
      'default_revision',
    ]);
    $expected = ['default_langcode', 'default_revision'];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));

    // Test adding both field and extra columns.
    $table_mapping->addFieldColumns('test', [
      'id',
      'name',
      'type',
      'description',
      'owner',
    ]);
    $table_mapping->addExtraColumns('test', [
      'default_langcode',
      'default_revision',
    ]);
    $expected = [
      'id',
      'name',
      'type',
      'description__value',
      'description__format',
      'owner__target_id',
      'owner__target_revision_id',
      'default_langcode',
      'default_revision',
    ];
    $this->assertSame($expected, $table_mapping->getAllColumns('test'));
  }

  /**
   * Tests DefaultTableMapping::getFieldNames().
   *
   * @covers ::getFieldNames()
   * @covers ::addFieldColumns()
   */
  public function testGetFieldNames() {
    // The storage definitions are only used in getColumnMapping() so we do not
    // need to provide any here.
    $table_mapping = new DefaultTableMapping([]);

    // Test that requesting the list of field names for a table for which no
    // fields have been added does not fail.
    $this->assertSame([], $table_mapping->getFieldNames('foo'));

    $return = $table_mapping->addFieldColumns('foo', ['id', 'name', 'type']);
    $this->assertSame($table_mapping, $return);
    $expected = ['id', 'name', 'type'];
    $this->assertSame($expected, $table_mapping->getFieldNames('foo'));
    $this->assertSame([], $table_mapping->getFieldNames('bar'));

    $return = $table_mapping->addFieldColumns('bar', ['description', 'owner']);
    $this->assertSame($table_mapping, $return);
    $expected = ['description', 'owner'];
    $this->assertSame($expected, $table_mapping->getFieldNames('bar'));
    // Test that the previously added field names are unaffected.
    $expected = ['id', 'name', 'type'];
    $this->assertSame($expected, $table_mapping->getFieldNames('foo'));
  }

  /**
   * Tests DefaultTableMapping::getColumnMapping().
   *
   * @covers ::__construct()
   * @covers ::getColumnMapping
   */
  public function testGetColumnMapping() {
    $definitions['test'] = $this->setUpDefinition([]);
    $table_mapping = new DefaultTableMapping($definitions);
    $expected = [];
    $this->assertSame($expected, $table_mapping->getColumnMapping('test'));

    $definitions['test'] = $this->setUpDefinition(['value']);
    $table_mapping = new DefaultTableMapping($definitions);
    $expected = ['value' => 'test'];
    $this->assertSame($expected, $table_mapping->getColumnMapping('test'));

    $definitions['test'] = $this->setUpDefinition(['value', 'format']);
    $table_mapping = new DefaultTableMapping($definitions);
    $expected = ['value' => 'test__value', 'format' => 'test__format'];
    $this->assertSame($expected, $table_mapping->getColumnMapping('test'));
  }

  /**
   * Tests DefaultTableMapping::getExtraColumns().
   *
   * @covers ::getExtraColumns()
   * @covers ::addExtraColumns()
   */
  public function testGetExtraColumns() {
    // The storage definitions are only used in getColumnMapping() so we do not
    // need to provide any here.
    $table_mapping = new DefaultTableMapping([]);

    // Test that requesting the list of field names for a table for which no
    // fields have been added does not fail.
    $this->assertSame([], $table_mapping->getExtraColumns('foo'));

    $return = $table_mapping->addExtraColumns('foo', ['id', 'name', 'type']);
    $this->assertSame($table_mapping, $return);
    $expected = ['id', 'name', 'type'];
    $this->assertSame($expected, $table_mapping->getExtraColumns('foo'));
    $this->assertSame([], $table_mapping->getExtraColumns('bar'));

    $return = $table_mapping->addExtraColumns('bar', ['description', 'owner']);
    $this->assertSame($table_mapping, $return);
    $expected = ['description', 'owner'];
    $this->assertSame($expected, $table_mapping->getExtraColumns('bar'));
    // Test that the previously added field names are unaffected.
    $expected = ['id', 'name', 'type'];
    $this->assertSame($expected, $table_mapping->getExtraColumns('foo'));
  }

  /**
   * Sets up a field storage definition for the test.
   *
   * @param array $column_names
   *   An array of column names for the storage definition.
   *
   * @return \Drupal\Core\Field\FieldStorageDefinitionInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected function setUpDefinition(array $column_names) {
    $definition = $this->getMock('Drupal\Core\Field\FieldStorageDefinitionInterface');
    $definition->expects($this->any())
      ->method('getColumns')
      ->will($this->returnValue(array_fill_keys($column_names, [])));
    return $definition;
  }

}
