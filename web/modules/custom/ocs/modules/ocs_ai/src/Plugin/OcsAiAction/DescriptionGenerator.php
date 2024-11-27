<?php

declare(strict_types=1);

namespace Drupal\ocs_ai\Plugin\OcsAiAction;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\ocs_ai\Attribute\OcsAiAction;
use Drupal\ocs_ai\OcsAiActionPluginBase;
use Drupal\ocs_car\Entity\Interface\CarInterface;

/**
 * Defines the Car description generator action.
 */
#[OcsAiAction(
  id: DescriptionGenerator::PLUGIN_ID,
  label: new TranslatableMarkup("Car description generation action")
)]
class DescriptionGenerator extends OcsAiActionPluginBase {

  public const string PLUGIN_ID = 'car_description_generator';

  private const array FIELD_MAP = [
    'brand' => 'Марка',
    'model' => 'Модель',
    'body_type' => 'Тип кузова',
    'color' => 'Колір',
    'year' => 'Рік випуску',
    'transmission_type' => 'Тип коробки',
    'fuel_type' => 'Тип пального',
    'kilometrage' => 'Пробіг',
    'price' => 'Ціна',
  ];

  /**
   * {@inheritdoc}
   */
  public function do(
    mixed $payload,
  ): ?string {
    $car = $this->validateCarPayload($payload);
    if (!$car) {
      return NULL;
    }

    $car_details = $this->extractCarDetails($car);
    if (empty($car_details)) {
      return NULL;
    }

    $query = $this->generateQuery($car_details);
    return $query ? $this->call($query) : NULL;
  }

  /**
   * Validates the car payload.
   */
  private function validateCarPayload(
    mixed $payload,
  ): ?CarInterface {
    return ($payload['car'] ?? NULL) instanceof CarInterface ? $payload['car'] : NULL;
  }

  /**
   * Extracts car details into an array.
   */
  private function extractCarDetails(
    CarInterface $car,
  ): array {
    return array_filter([
      'brand' => $car->getBrand(),
      'model' => $car->getModel(),
      'body_type' => $car->getBodyType(),
      'color' => $car->getColor(),
      'year' => $car->getYear(),
      'transmission_type' => $car->getTransmissionType(),
      'fuel_type' => $car->getFuelType(),
      'kilometrage' => $car->getKilometrage(),
      'price' => $car->getPrice(),
    ]);
  }

  /**
   * Generates the query string for AI.
   */
  protected function generateQuery(
    mixed $payload,
  ): mixed {
    $prompt = "Згенерувати текст для обʼяви по продажу автомобіля: ";
    foreach ($payload as $key => $value) {
      $prompt .= sprintf("%s: %s, ", $this->mapFieldToLabel($key), $value);
    }

    $prompt .= "Опис повинен містити 200-300 символів, включати ключові характеристики, технічні дані та основні переваги автомобіля.
    Українською
    Стилистика має бути дружньою, максимально неформальною та викликаючою довіру.
    Треба максимально використовувати сленг та скороченя(кілометри в тис км, ціну в К(типу 12к$, 1,5$).
    Уникайте забороненого контенту та переконайтеся, що опис відповідає політикам платформи.
    Треба використовувати використовувати емоджі(🤑🏽👌💸).

    ФОРМАТ:
марка, модель, тип кузова(без підписів, можна скорочувати або використовувати сленг)/перехід на новий рядок
тип коробки, тип пального(без підписів, українського)/перехід на новий рядок
рік, пробіг(без підписів)/перехід на новий рядок

[текстовий опис]
[неформальне запрошення на тестдрайв

[ціна]
";

    return $prompt;
  }

  /**
   * Maps car attributes to their Ukrainian labels.
   */
  private function mapFieldToLabel(
    string $field,
  ): string {
    return self::FIELD_MAP[$field] ?? $field;
  }

  /**
   * Makes an AI call to generate a description.
   */
  protected function call(
    mixed $payload,
  ): ?string {
    if (empty($payload)) {
      return NULL;
    }

    try {
      $response = $this->aiClient->query($payload);
      return is_string($response) && !empty($response) ? trim($response) : NULL;
    }
    catch (\Exception $e) {
      \Drupal::logger('ocs_ai')->error('AI Client Error: @message', [
        '@message' => $e->getMessage(),
      ]);

      return NULL;
    }
  }

}
