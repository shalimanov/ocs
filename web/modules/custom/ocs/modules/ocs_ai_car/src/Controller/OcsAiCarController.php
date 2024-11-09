<?php

declare(strict_types=1);

namespace Drupal\ocs_ai_car\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for OCS AI Car routes.
 */
final class OcsAiCarController extends ControllerBase {

  /**
   * @var \Drupal\ocs_ai\Service\ChatGPTClient
   *
   * ChatGPT client service
   */
  protected ChatGPTClient $chatGptClient;

  public static function create(
    ContainerInterface $container
  ): OcsAiCarController {
    $instance = parent::create($container);

    $instance->chatGptClient = $container->get(ChatGPTClient::class);

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


    return $build;
  }

}
