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
class ChatGPTClient implements AIClientInterface {

  public const string CLIENT_ID = 'chatgpt';

  private const string CHAT_COMPLETION_ENDPOINT = 'https://api.openai.com/v1/chat/completions';

  private const string COMPLETION_ENDPOINT = 'https://api.openai.com/v1/completions';

  private const array CHAT_MODELS = ['gpt-3.5-turbo', 'gpt-4'];

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected ClientInterface $httpClient,
    protected LoggerInterface $logger,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function query(mixed $request): mixed {
    if (empty($request)) {
      return $this->handleEmptyRequest();
    }

    $clientConfig = $this->getClientConfig();
    if (!$clientConfig) {
      return $this->handleMissingConfig();
    }

    [$apiKey, $model] = $this->extractApiKeyAndModel($clientConfig);
    if (!$apiKey) {
      return $this->handleMissingApiKey();
    }

    $endpoint = $this->determineEndpoint($model);
    $payload = $this->buildPayload($model, $request);

    return $this->executeRequest($endpoint, $apiKey, $payload, $model);
  }

  /*** Private Methods ***/

  /**
   * Handles empty request scenario.
   */
  private function handleEmptyRequest(): string {
    $message = 'Empty request provided to AI query.';
    $this->logger->error($message);
    return $message;
  }

  /**
   * Retrieves the client configuration.
   */
  private function getClientConfig(): ?array {
    $config = $this->configFactory->get('ocs_ai.settings');
    $clients = $config->get('settings')['clients'] ?? [];

    foreach ($clients as $client) {
      if ($client['client_id'] === self::CLIENT_ID) {
        return $client;
      }
    }
    return NULL;
  }

  /**
   * Handles missing configuration scenario.
   */
  private function handleMissingConfig(): string {
    $message = 'No configuration found for the selected AI client.';
    $this->logger->error($message);
    return $message;
  }

  /**
   * Extracts API key and model from client configuration.
   */
  private function extractApiKeyAndModel(array $clientConfig): array {
    $apiKey = $clientConfig['api_key'] ?? NULL;
    $model = $clientConfig['model'] ?? 'gpt-3.5-turbo';
    return [$apiKey, $model];
  }

  /**
   * Handles missing API key scenario.
   */
  private function handleMissingApiKey(): string {
    $message = 'API key is missing for client ID.';
    $this->logger->error($message);
    return $message;
  }

  /**
   * Determines the API endpoint based on the model.
   */
  private function determineEndpoint(string $model): string {
    return in_array($model, self::CHAT_MODELS)
      ? self::CHAT_COMPLETION_ENDPOINT
      : self::COMPLETION_ENDPOINT;
  }

  /**
   * Builds the payload for the API request.
   */
  private function buildPayload(
    string $model,
    mixed $request,
  ): array {
    $maxTokens = 1000;
    if (in_array($model, self::CHAT_MODELS)) {
      return [
        'model' => $model,
        'messages' => [
          ['role' => 'user', 'content' => $request],
        ],
        'max_tokens' => $maxTokens,
      ];
    }
    else {
      return [
        'model' => $model,
        'prompt' => $request,
        'max_tokens' => $maxTokens,
      ];
    }
  }

  /**
   * Executes the API request and handles the response.
   */
  private function executeRequest(
    string $endpoint,
    string $apiKey,
    array $payload,
    string $model,
  ): string {
    try {
      $response = $this->httpClient->request('POST', $endpoint, [
        'headers' => [
          'Authorization' => "Bearer {$apiKey}",
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
      return $this->handleGeneralException($e);
    }
  }

  /**
   * Parses the API response data.
   */
  private function parseResponse(
    array $data,
    string $model,
  ): string {
    if (in_array($model, self::CHAT_MODELS)) {
      return $data['choices'][0]['message']['content'] ?? 'No response';
    }
    else {
      return $data['choices'][0]['text'] ?? 'No response';
    }
  }

  /**
   * Handles ClientException errors.
   */
  private function handleClientException(ClientException $e): string {
    if ($e->getCode() === 429) {
      $message = 'API quota exceeded. Please try again later.';
      $this->logger->warning('Exceeded OpenAI API quota. Check billing and usage limits.');
    }
    else {
      $message = 'Error querying AI';
      $this->logger->error('Error querying AI: ' . $e->getMessage());
    }
    return $message;
  }

  /**
   * Handles general exceptions.
   */
  private function handleGeneralException(\Exception $e): string {
    $this->logger->error('Unexpected error querying AI: ' . $e->getMessage());
    return 'Error querying AI';
  }

}
