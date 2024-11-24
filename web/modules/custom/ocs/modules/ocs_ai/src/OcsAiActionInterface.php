<?php

declare(strict_types=1);

namespace Drupal\ocs_ai;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Interface for ocs_ai_action plugins.
 */
interface OcsAiActionInterface extends ContainerFactoryPluginInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

  /**
   * Makes AI action.
   */
  public function do(
    mixed $payload,
  ): mixed;

}
