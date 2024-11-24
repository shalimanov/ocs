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
final class ChatGPTClient implements AIClientInterface {

  public const CLIENT_ID = 'chatgpt';

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
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
      $this->logger->error('Empty request provided to AI query.');
      return 'Empty request provided to AI query.';
    }

    $config = $this->configFactory->get('ocs_ai.settings');
    $settings = $config->get('settings') ?? [];
    $clients = $settings['clients'] ?? [];
    $client_config = NULL;

    foreach ($clients as $client) {
      if ($client['client_id'] === self::CLIENT_ID) {
        $client_config = $client;
        break;
      }
    }

    if (empty($client_config)) {
      $this->logger->error('No configuration found for the selected AI client.');
      return 'No configuration found for the selected AI client.';
    }

    $api_key = $client_config['api_key'] ?? NULL;
    $model = $client_config['model'] ?? 'gpt-3.5-turbo';

    if (!$api_key) {
      $this->logger->error('API key is missing for client ID.');
      return 'API key is missing for client ID.';
    }

    $endpoint = in_array($model, ['gpt-3.5-turbo', 'gpt-4'])
      ? 'https://api.openai.com/v1/chat/completions'
      : 'https://api.openai.com/v1/completions';

    $payload = in_array($model, ['gpt-3.5-turbo', 'gpt-4o'])
      ? [
        'model' => $model,
        'messages' => [
          ['role' => 'user', 'content' => $request],
        ],
        'max_tokens' => 1000,
      ]
      : [
        'model' => $model,
        'prompt' => $request,
        'max_tokens' => 1000,
      ];

    try {
      $response = $this->httpClient->request('POST', $endpoint, [
        'headers' => [
          'Authorization' => "Bearer {$api_key}",
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
      ]);

      $data = Json::decode($response->getBody()->getContents());

      if (in_array($model, ['gpt-3.5-turbo', 'gpt-4'])) {
        return $data['choices'][0]['message']['content'] ?? 'No response';
      }
      else {
        return $data['choices'][0]['text'] ?? 'No response';
      }
    }
    catch (ClientException $e) {
      if ($e->getCode() === 429) {
        $this->logger->warning('Exceeded OpenAI API quota. Check billing and usage limits.');
        return 'API quota exceeded. Please try again later.';
      }
      $this->logger->error('Error querying AI: ' . $e->getMessage());
      return 'Error querying AI';
    }
    catch (\Exception $e) {
      $this->logger->error('Unexpected error querying AI: ' . $e->getMessage());
      return 'Error querying AI';
    }
  }

}
