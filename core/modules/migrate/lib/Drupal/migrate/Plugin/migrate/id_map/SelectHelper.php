<?php

/**
 * @file
 * Contains \Drupal\migrate\Plugin\migrate\id_map\SelectHelper.
 */

namespace Drupal\migrate\Plugin\migrate\id_map;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PlaceholderInterface;
use Drupal\Core\Database\Query\SelectInterface;

class SelectHelper implements SelectInterface {

  /**
   * @var string
   */
  protected $tablename;

  /**
   * @param string $tablename
   */
  public function __construct($tablename) {
    $this->tablename = $tablename;
  }

  /**
   * @return string
   */
  public function __toString() {
    return "SELECT * FROM $this->tablename";
  }

  /**
   * {@inheritdoc}
   */
  public function arguments() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function addTag($tag) {
  }

  /**
   * {@inheritdoc}
   */
  public function hasTag($tag) {
  }

  /**
   * {@inheritdoc}
   */
  public function hasAllTags() {
  }

  /**
   * {@inheritdoc}
   */
  public function hasAnyTag() {
  }

  /**
   * {@inheritdoc}
   */
  public function addMetaData($key, $object) {
  }

  /**
   * {@inheritdoc}
   */
  public function getMetaData($key) {
  }

  /**
   * {@inheritdoc}
   */
  public function condition($field, $value = NULL, $operator = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function where($snippet, $args = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function isNull($field) {
  }

  /**
   * {@inheritdoc}
   */
  public function isNotNull($field) {
  }

  /**
   * {@inheritdoc}
   */
  public function exists(SelectInterface $select) {

  }

  /**
   * {@inheritdoc}
   */
  public function notExists(SelectInterface $select) {
  }

  /**
   * {@inheritdoc}
   */
  public function &conditions() {
  }

  /**
   * {@inheritdoc}
   */
  public function compile(Connection $connection, PlaceholderInterface $queryPlaceholder) {
  }

  /**
   * {@inheritdoc}
   */
  public function compiled() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function conditionGroupFactory($conjunction = 'AND') {
  }

  /**
   * {@inheritdoc}
   */
  public function andConditionGroup() {
  }

  /**
   * {@inheritdoc}
   */
  public function orConditionGroup() {
  }

  /**
   * {@inheritdoc}
   */
  public function extend($extender_name) {
  }

  /**
   * {@inheritdoc}
   */
  public function uniqueIdentifier() {
  }

  /**
   * {@inheritdoc}
   */
  public function nextPlaceholder() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getFields() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getExpressions() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getOrderBy() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getGroupBy() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getTables() {
  }

  /**
   * {@inheritdoc}
   */
  public function &getUnion() {

  }

  /**
   * {@inheritdoc}
   */
  public function getArguments(PlaceholderInterface $queryPlaceholder = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function distinct($distinct = TRUE) {
  }

  /**
   * {@inheritdoc}
   */
  public function addField($table_alias, $field, $alias = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function fields($table_alias, array $fields = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function addExpression($expression, $alias = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function join($table, $alias = NULL, $condition = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function innerJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function leftJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function rightJoin($table, $alias = NULL, $condition = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function addJoin($type, $table, $alias = NULL, $condition = NULL, $arguments = array()) {
  }

  /**
   * {@inheritdoc}
   */
  public function orderBy($field, $direction = 'ASC') {
  }

  /**
   * {@inheritdoc}
   */
  public function orderRandom() {
  }

  /**
   * {@inheritdoc}
   */
  public function range($start = NULL, $length = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function union(SelectInterface $query, $type = '') {
  }

  /**
   * {@inheritdoc}
   */
  public function groupBy($field) {
  }

  /**
   * {@inheritdoc}
   */
  public function countQuery() {
  }

  /**
   * {@inheritdoc}
   */
  public function isPrepared() {
  }

  /**
   * {@inheritdoc}
   */
  public function preExecute(SelectInterface $query = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function havingCondition($field, $value = NULL, $operator = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function __clone() {
  }

  /**
   * {@inheritdoc}
   */
  public function forUpdate($set = TRUE) {
  }
}
