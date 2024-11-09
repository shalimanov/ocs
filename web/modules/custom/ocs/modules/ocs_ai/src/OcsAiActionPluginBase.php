<?php

declare(strict_types=1);

namespace Drupal\ocs_ai;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for ocs_ai_action plugins.
 */
abstract class OcsAiActionPluginBase extends PluginBase implements OcsAiActionInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
