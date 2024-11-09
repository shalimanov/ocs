<?php

namespace Drupal\Tests\ocs_ai\Unit;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Drupal\ocs_ai\Service\AIClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Psr7\Request;

/**
 * Tests the ChatGPTClient.
 *
 * @group ocs_ai
 */
class ChatGPTClientTest extends UnitTestCase {

  /**
   * @var \Drupal\ocs_ai\Service\ChatGPTClient
   */
  protected ChatGPTClient $chatGPTClient;

  /**
   * Mock configuration for the client.
   */
  protected array $clientConfig = [
    'clients' => [
      [
        'client_id' => 'chatgpt',
        'api_key' => 'test-key',
        'model' => 'gpt-3.5-turbo',
      ],
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $config = $this->createMock(Config::class);
    $config->method('get')
      ->with('clients')
      ->willReturn($this->clientConfig['clients']);

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $httpClient = $this->createMock(ClientInterface::class);
    $logger = $this->createMock(LoggerInterface::class);

    $this->chatGPTClient = new ChatGPTClient($configFactory, $httpClient, $logger);
  }

  /**
   * Tests instantiation of the service.
   */
  public function testServiceInstance(): void {
    $this->assertInstanceOf(AIClient::class, $this->chatGPTClient);
  }

  /**
   * Tests handling of an empty request.
   */
  public function testEmptyRequestHandling(): void {
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('error')
      ->with('Empty request provided to AI query.');

    $client = new ChatGPTClient($this->createMock(ConfigFactoryInterface::class), $this->createMock(ClientInterface::class), $logger);

    $result = $client->query('');
    $this->assertEquals('Empty request provided to AI query.', $result);
  }

  /**
   * Tests handling of a missing API key.
   */
  public function testMissingApiKey(): void {
    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $config = $this->createMock(Config::class);
    $config->method('get')->willReturn(['clients' => [['client_id' => 'chatgpt', 'model' => 'gpt-3.5-turbo']]]);
    $configFactory->method('get')->willReturn($config);

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('error')
      ->with('API key is missing for client ID.');

    $client = new ChatGPTClient($configFactory, $this->createMock(ClientInterface::class), $logger);
    $result = $client->query('Test query');

    $this->assertEquals('API key is missing for client ID.', $result);
  }

  /**
   * Tests a successful query with a chat model.
   */
  public function testSuccessfulChatQuery(): void {
    $response = new Response(200, [], Json::encode(['choices' => [['message' => ['content' => 'Test response']]]]));
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST')
      ->willReturn($response);

    $client = new ChatGPTClient($this->createMock(ConfigFactoryInterface::class), $httpClient, $this->createMock(LoggerInterface::class));
    $result = $client->query('Test query');

    $this->assertEquals('Test response', $result);
  }

  /**
   * Tests handling of an API quota exceeded error.
   */
  public function testQuotaExceeded(): void {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST')
      ->willThrowException(new ClientException('Quota exceeded', new Request('POST', 'test'), new Response(429)));

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('warning')
      ->with('Exceeded OpenAI API quota. Check billing and usage limits.');

    $client = new ChatGPTClient($this->createMock(ConfigFactoryInterface::class), $httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('API quota exceeded. Please try again later.', $result);
  }

  /**
   * Tests unexpected errors during the request.
   */
  public function testUnexpectedErrorHandling(): void {
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST')
      ->willThrowException(new \Exception('Unexpected error'));

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('error')
      ->with('Unexpected error querying AI for client ID @client_id: @message', [
        '@client_id' => ChatGPTClient::CLIENT_ID,
        '@message' => 'Unexpected error',
      ]);

    $client = new ChatGPTClient($this->createMock(ConfigFactoryInterface::class), $httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('Error querying AI', $result);
  }

}
