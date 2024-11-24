<?php

namespace Drupal\Tests\ocs_ai\Unit;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\ocs_ai\Service\ChatGPTClient;
use Drupal\ocs_ai\Service\AIClientInterface;
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
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClient;

  /**
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $mockSettings = [
          'settings' => [
            'clients' => [
              [
                'client_id' => 'chatgpt',
                'api_key' => 'test-key',
                'model' => 'gpt-3.5-turbo',
              ],
            ],
          ],
        ];
        return $mockSettings[$key] ?? NULL;
      });

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $this->httpClient = $this->createMock(ClientInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);

    $this->chatGPTClient = new ChatGPTClient($this->configFactory, $this->httpClient, $this->logger);
  }

  /**
   * Tests instantiation of the service.
   */
  public function testServiceInstance(): void {
    $this->assertInstanceOf(AIClientInterface::class, $this->chatGPTClient);
  }

  /**
   * Tests handling of a missing API key.
   */
  public function testMissingApiKey(): void {
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $mockSettings = [
          'settings' => [
            'clients' => [
              [
                'client_id' => 'chatgpt',
                'api_key' => NULL,
                'model' => 'gpt-3.5-turbo',
              ],
            ],
          ],
        ];
        return $mockSettings[$key] ?? NULL;
      });

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('error')
      ->with('API key is missing for client ID.');

    $client = new ChatGPTClient($configFactory, $this->httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('API key is missing for client ID.', $result);
  }

  /**
   * Tests a successful query with a chat model.
   */
  public function testSuccessfulChatQuery(): void {
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $mockSettings = [
          'settings' => [
            'clients' => [
              [
                'client_id' => 'chatgpt',
                'api_key' => 'test-key',
                'model' => 'gpt-3.5-turbo',
              ],
            ],
          ],
        ];
        return $mockSettings[$key] ?? NULL;
      });

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $response = new Response(200, [], Json::encode(['choices' => [['message' => ['content' => 'Test response']]]]));
    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST', $this->anything(), $this->anything())
      ->willReturn($response);

    $logger = $this->createMock(LoggerInterface::class);

    $client = new ChatGPTClient($configFactory, $httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('Test response', $result);
  }

  /**
   * Tests handling of an API quota exceeded error.
   */
  public function testQuotaExceeded(): void {
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $mockSettings = [
          'settings' => [
            'clients' => [
              [
                'client_id' => 'chatgpt',
                'api_key' => 'test-key',
                'model' => 'gpt-3.5-turbo',
              ],
            ],
          ],
        ];
        return $mockSettings[$key] ?? NULL;
      });

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST', $this->anything(), $this->anything())
      ->willThrowException(new ClientException('Quota exceeded', new Request('POST', 'test'), new Response(429)));

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('warning')
      ->with('Exceeded OpenAI API quota. Check billing and usage limits.');

    $client = new ChatGPTClient($configFactory, $httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('API quota exceeded. Please try again later.', $result);
  }

  /**
   * Tests unexpected errors during the request.
   */
  public function testUnexpectedErrorHandling(): void {
    $config = $this->createMock(Config::class);
    $config->method('get')
      ->willReturnCallback(function ($key) {
        $mockSettings = [
          'settings' => [
            'clients' => [
              [
                'client_id' => 'chatgpt',
                'api_key' => 'test-key',
                'model' => 'gpt-3.5-turbo',
              ],
            ],
          ],
        ];
        return $mockSettings[$key] ?? NULL;
      });

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')
      ->with('ocs_ai.settings')
      ->willReturn($config);

    $httpClient = $this->createMock(ClientInterface::class);
    $httpClient->expects($this->once())
      ->method('request')
      ->with('POST', $this->anything(), $this->anything())
      ->willThrowException(new \Exception('Unexpected error'));

    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())
      ->method('error')
      ->with('Unexpected error querying AI: Unexpected error');

    $client = new ChatGPTClient($configFactory, $httpClient, $logger);
    $result = $client->query('Test query');

    $this->assertEquals('Error querying AI', $result);
  }

}
