<?php

declare(strict_types=1);

namespace Drupal\ocs_ai;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\ocs_ai\Attribute\OcsAiAction;

/**
 * OcsAiAction plugin manager.
 */
class OcsAiActionPluginManager extends DefaultPluginManager {

  /**
   * Constructs the object.
   */
  public function __construct(
    \Traversable $namespaces,
    CacheBackendInterface $cache_backend,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct('Plugin/OcsAiAction', $namespaces, $module_handler, OcsAiActionInterface::class, OcsAiAction::class);
    $this->alterInfo('ocs_ai_action_info');
    $this->setCacheBackend($cache_backend, 'ocs_ai_action_plugins');
  }

}
