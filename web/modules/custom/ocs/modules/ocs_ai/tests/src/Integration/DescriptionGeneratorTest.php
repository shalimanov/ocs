<?php

namespace Drupal\Tests\ocs_ai\Integration;

use Drupal\ocs_ai\Plugin\OcsAiAction\DescriptionGenerator;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Drupal\ocs_car\Entity\Car;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Integration test for the DescriptionGenerator.
 *
 * @group ocs_ai
 */
class DescriptionGeneratorTest extends UnitTestCase {

  /**
   * Tests the end-to-end description generation process.
   */
  public function testDescriptionGeneration(): void {
    $car = $this->createMock(Car::class);
    $car->method('getBrand')->willReturn('Honda');
    $car->method('getModel')->willReturn('Civic');
    $car->method('getBodyType')->willReturn('hatchback');
    $car->method('getColor')->willReturn('Blue');
    $car->method('getYear')->willReturn(2018);
    $car->method('getTransmissionType')->willReturn('manual');
    $car->method('getFuelType')->willReturn('diesel');
    $car->method('getKilometrage')->willReturn(30000);
    $car->method('getPrice')->willReturn('USD 15000');

    $mock_ai_client = $this->createMock(ChatGPTClient::class);
    $mock_ai_client->method('query')->willReturn('Generated car description');

    $container = new ContainerBuilder();
    $container->set('ocs_ai.client.chat_gpt', $mock_ai_client);
    \Drupal::setContainer($container);

    $configuration = [];
    $plugin_id = DescriptionGenerator::PLUGIN_ID;
    $plugin_definition = [
      'id' => $plugin_id,
      'label' => 'Car description generation action',
    ];

    $description_generator = DescriptionGenerator::create($container, $configuration, $plugin_id, $plugin_definition);

    $payload = ['car' => $car];
    $result = $description_generator->do($payload);

    $this->assertEquals('Generated car description', $result);
  }
}
