<?php

declare(strict_types=1);

namespace Drupal\ocs_ai\Plugin\OcsAiAction;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ocs_ai\Attribute\OcsAiAction;
use Drupal\ocs_ai\OcsAiActionPluginBase;

/**
 * Defines the Car description generator action.
 */
#[OcsAiAction(
  id: DescriptionGenerator::PLUGIN_ID,
  label: new TranslatableMarkup("Car description generation action")
)]
final class DescriptionGenerator extends OcsAiActionPluginBase {

  public const string PLUGIN_ID = 'car_description_generator';

}
