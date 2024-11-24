<?php

declare(strict_types=1);

namespace Drupal\ocs_ai;

use Drupal\Component\Plugin\PluginBase;
use Drupal\ocs_ai\Service\AIClientInterface;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for ocs_ai_action plugins.
 */
abstract class OcsAiActionPluginBase extends PluginBase implements OcsAiActionInterface {

  /**
   * @var \Drupal\ocs_ai\Service\AIClientInterface
   */
  protected AIClientInterface $aiClient;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );

    $instance->aiClient = $container->get('ocs_ai.client.chat_gpt');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * Generates query to AI.
   */
  abstract protected function generateQuery(
    mixed $payload,
  ): mixed;

  /**
   * Makes call to AI.
   */
  abstract protected function call(
    mixed $payload,
  ): mixed;

}
