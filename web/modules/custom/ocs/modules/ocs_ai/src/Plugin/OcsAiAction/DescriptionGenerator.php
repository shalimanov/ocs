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

  /**
   * {@inheritdoc}
   */
  public function do(
    mixed $payload,
  ): mixed {
    $car = $payload['car'] ?? NULL;
    if (!$car instanceof CarInterface) {
      return NULL;
    }

    $car_details = [
      'brand' => $car->getBrand(),
      'model' => $car->getModel(),
      'body_type' => $car->getBodyType(),
      'color' => $car->getColor(),
      'year' => $car->getYear(),
      'transmission_type' => $car->getTransmissionType(),
      'fuel_type' => $car->getFuelType(),
      'kilometrage' => $car->getKilometrage(),
      'price' => $car->getPrice(),
    ];

    $car_details = array_filter($car_details);
    if (empty($car_details)) {
      return NULL;
    }

    $query = $this->generateQuery($car_details);
    if (empty($query)) {
      return NULL;
    }

    return $this->call($query);
  }

  /**
   * {@inheritdoc}
   */
  protected function generateQuery(
    mixed $payload,
  ): mixed {
    if (empty($payload) || !is_array($payload)) {
      throw new \InvalidArgumentException('Invalid or missing payload for generating query.');
    }

    $prompt = "Згенерувати текст для обʼяви по продажу автомобіля: ";
    foreach ($payload as $key => $value) {
      $prompt .= sprintf("%s: %s, ", $this->mapFieldToLabel($key), $value);
    }

    $prompt .= "Опис повинен містити 150-200 символів, включати ключові характеристики, технічні дані та основні переваги автомобіля.
    Українською
    Стилистика має бути дружньою, максимально неформальною та викликаючою довіру.
    Треба максимально використовувати сленг та скороченя(кілометри в тис км, ціну в К(типу 12к$, 1,5$).
    Уникайте забороненого контенту та переконайтеся, що опис відповідає політикам платформи.
    Треба використовувати використовувати емоджі(🤑🏽👌💸).
    сформатуй з використостанням html p
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
   * Maps car attributes to their corresponding Ukrainian labels.
   *
   * @param string $field
   *   The field name.
   *
   * @return string
   *   The label in Ukrainian.
   */
  private function mapFieldToLabel(string $field): string {
    $field_map = [
      'brand' => 'Марка',
      'model' => 'Модель',
      'body_type' => 'Тип кузова',
      'color' => 'Колір',
      'condition' => 'Стан',
      'year' => 'Рік випуску',
      'kilometrage' => 'Пробіг',
      'price' => 'Ціна',
    ];

    return $field_map[$field] ?? $field;
  }

  /**
   * {@inheritdoc}
   */
  protected function call(
    mixed $payload,
  ): mixed {
    if (empty($payload) || !is_string($payload)) {
      throw new \InvalidArgumentException('Invalid payload for AI call. Expected a string query.');
    }

    try {
      $response = $this->aiClient->query($payload);

      if (is_string($response) && !empty($response)) {
        return trim($response);
      }

      throw new \RuntimeException('AI response did not contain a valid output.');
    }
    catch (\Exception $e) {
      \Drupal::logger('ocs_ai')->error('AI Client Error: @message', [
        '@message' => $e->getMessage(),
      ]);

      return NULL;
    }
  }

}
