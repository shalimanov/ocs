<?php

declare(strict_types=1);

namespace Drupal\ocs_ai;

/**
 * Interface for ocs_ai_action plugins.
 */
interface OcsAiActionInterface {

  /**
   * Returns the translated plugin label.
   */
  public function label(): string;

}
