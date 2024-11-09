<?php

namespace Drupal\ocs_ai\Service;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;

/**
 * Class ChatGPTClient provides AI client for OpenAI ChatGPT.
 */
final class ChatGPTClient implements AIClient {

  public const CLIENT_ID = 'chatgpt';

  public function __construct(
    protected ConfigFactoryInterface $config_factory,
    protected ClientInterface $httpClient,
    protected LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function query(
    mixed $request,
  ): mixed {
    if (empty($request)) {
      return $this->logAndReturnError('Empty request provided to AI query.');
    }

    $client_config = $this->getClientConfig();
    if (empty($client_config)) {
      return $this->logAndReturnError('No configuration found for the selected AI client.');
    }

    $api_key = $client_config['api_key'] ?? NULL;
    $model = $client_config['model'] ?? 'gpt-3.5-turbo';

    if (!$api_key) {
      return $this->logAndReturnError('API key is missing for client ID.');
    }

    $endpoint = $this->getEndpointForModel($model);
    $payload = $this->buildPayload($model, $request);

    return $this->executeRequest($endpoint, $api_key, $payload, $model);
  }

  /**
   * Fetches client configuration.
   */
  private function getClientConfig(): ?array {
    $config = $this->config_factory->get('ocs_ai.settings');
    $clients = $config->get('settings')['clients'] ?? [];
    $client = array_filter($clients, fn(
      $client,
    ) => $client['client_id'] === self::CLIENT_ID);
    return reset($client) ?: NULL;
  }

  /**
   * Returns the correct endpoint based on the model type.
   */
  private function getEndpointForModel(string $model): string {
    return in_array($model, ['gpt-3.5-turbo', 'gpt-4'])
      ? 'https://api.openai.com/v1/chat/completions'
      : 'https://api.openai.com/v1/completions';
  }

  /**
   * Builds the request payload.
   */
  private function buildPayload(
    string $model,
    mixed $request,
  ): array {
    $is_chat_model = in_array($model, ['gpt-3.5-turbo', 'gpt-4']);
    return $is_chat_model
      ? [
        'model' => $model,
        'messages' => [
          ['role' => 'user', 'content' => $request],
        ],
        'max_tokens' => 100,
      ]
      : [
        'model' => $model,
        'prompt' => $request,
        'max_tokens' => 100,
      ];
  }

  /**
   * Executes the HTTP request and handles the response.
   */
  private function executeRequest(
    string $endpoint,
    string $api_key,
    array $payload,
    string $model,
  ): mixed {
    try {
      $response = $this->httpClient
        ->post($endpoint, [
          'headers' => [
            'Authorization' => "Bearer {$api_key}",
            'Content-Type' => 'application/json',
          ],
          'json' => $payload,
        ]);

      $data = Json::decode($response->getBody()->getContents());
      return $this->parseResponse($data, $model);
    }
    catch (ClientException $e) {
      return $this->handleClientException($e);
    }
    catch (\Exception $e) {
      $this->logger
        ->error('Unexpected error querying AI for client ID @client_id: @message', [
          '@client_id' => self::CLIENT_ID,
          '@message' => $e->getMessage(),
        ]);
      return 'Error querying AI';
    }
  }

  /**
   * Parses the API response based on the model type.
   */
  private function parseResponse(
    array $data,
    string $model,
  ): string {
    $is_chat_model = in_array($model, ['gpt-3.5-turbo', 'gpt-4']);
    return $is_chat_model
      ? $data['choices'][0]['message']['content'] ?? 'No response'
      : $data['choices'][0]['text'] ?? 'No response';
  }

  /**
   * Handles client exceptions with specific response codes.
   */
  private function handleClientException(
    ClientException $e,
  ): string {
    if ($e->getCode() === 429) {
      $this->logger
        ->warning('Exceeded OpenAI API quota. Check billing and usage limits.');
      return 'API quota exceeded. Please try again later.';
    }
    $this->logger
      ->error('Error querying AI for client ID @client_id: @message', [
        '@client_id' => self::CLIENT_ID,
        '@message' => $e->getMessage(),
      ]);
    return 'Error querying AI';
  }

  /**
   * Logs an error message and returns it.
   */
  private function logAndReturnError(
    string $message,
  ): string {
    $this->logger
      ->error($message);
    return $message;
  }

}
