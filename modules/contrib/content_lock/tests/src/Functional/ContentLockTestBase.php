<?php

declare(strict_types=1);

namespace Drupal\Tests\content_lock\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for content_lock tests.
 */
abstract class ContentLockTestBase extends BrowserTestBase {

  use ContentLockTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'entity_test',
    'content_lock',
  ];

}
