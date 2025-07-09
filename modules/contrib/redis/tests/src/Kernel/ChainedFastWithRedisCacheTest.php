<?php

namespace Drupal\Tests\redis\Kernel;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\ChainedFastBackend;
use Drupal\Core\Cache\PhpBackend;

/**
 * Tests Redis cache backend using GenericCacheBackendUnitTestBase.
 *
 * @group redis
 */
class ChainedFastWithRedisCacheTest extends RedisCacheTest {

  /**
   * Creates a new instance of ChainedFastBackend.
   *
   * @return \Drupal\Core\Cache\ChainedFastBackend
   *   A new ChainedFastBackend object.
   */
  protected function createCacheBackend($bin) {
    $consistent_backend = \Drupal::service('cache.backend.redis')->get($bin);
    $consistent_backend->setMinTtl(10);
    $fast_backend = new PhpBackend($bin, \Drupal::service('cache_tags.invalidator.checksum'), \Drupal::service(TimeInterface::class));
    $backend = new ChainedFastBackend($consistent_backend, $fast_backend, $bin);
    // Explicitly register the cache bin as it can not work through the
    // cache bin list in the container.
    \Drupal::service('cache_tags.invalidator')->addInvalidator($backend);
    return $backend;
  }

}
