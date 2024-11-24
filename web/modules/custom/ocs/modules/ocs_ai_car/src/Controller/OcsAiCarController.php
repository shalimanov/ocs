<?php

declare(strict_types=1);

namespace Drupal\ocs_ai_car\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ocs_ai\OcsAiActionPluginManager;
use Drupal\ocs_ai\Plugin\OcsAiAction\DescriptionGenerator;
use Drupal\ocs_ai\Service\AIClientInterface;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Drupal\ocs_car\Entity\Car;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for OCS AI Car routes.
 */
final class OcsAiCarController extends ControllerBase {

  /**
   * @var \Drupal\ocs_ai\Service\AIClientInterface
   *
   * ChatGPT client service
   */
  protected AIClientInterface $chatGptClient;

  /**
   * @var \Drupal\ocs_ai\OcsAiActionPluginManager|null
   */
  protected OcsAiActionPluginManager|null $aiActionManager;

  public static function create(
    ContainerInterface $container
  ): OcsAiCarController {
    $instance = parent::create($container);

    $instance->chatGptClient = $container->get('ocs_ai.client.chat_gpt');
    $instance->aiActionManager = $container->get('plugin.manager.ocs_ai_action');

    return $instance;
  }

  /**
   * Builds the response.
   */
  public function __invoke(): array {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    $response = $this->chatGptClient
      ->query('how do you do');
    $build['response'] = [
      '#type' => 'item',
      '#markup' => $response,
    ];

    $car = Car::load(1);

    $description_generator = $this->aiActionManager
      ->createInstance(DescriptionGenerator::PLUGIN_ID);

    $result = $description_generator->do(['car' => $car]);

    $build['generated description'] = [
      '#type' => 'item',
      '#markup' => $result,
    ];



    return $build;
  }

}
