<?php

declare(strict_types=1);

namespace Drupal\ocs_ai\Service;

/**
 * Provides interface for different AI Clients.
 */
interface AIClient {

  /**
   * Sends a query to the AI service and returns the response.
   *
   * @param mixed $request
   *   The input query.
   *
   * @return mixed
   *   The AI service response.
   */
  public function query(
    mixed $request,
  ): mixed;

}
