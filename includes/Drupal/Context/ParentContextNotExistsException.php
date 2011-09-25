<?php

namespace Drupal\Context;

/**
 * Exception thrown when attempting to fall through to a parent context object
 * that already got garbace collected
 */
class ParentContextNotExistsException extends ContextException {}
