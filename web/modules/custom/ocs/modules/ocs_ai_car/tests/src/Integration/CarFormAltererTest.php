<?php

namespace Drupal\Tests\ocs_ai_car\Integration;

use Drupal\Core\Form\FormState;
use Drupal\ocs_ai\Plugin\OcsAiAction\DescriptionGenerator;
use Drupal\ocs_ai\Service\AIClientInterface;
use Drupal\ocs_ai_car\FormAlterer\CarFormAlterer;
use Drupal\ocs_car\Entity\Car;
use Drupal\ocs_car\Form\CarForm;
use Drupal\Tests\UnitTestCase;

/**
 * Integration test for the CarFormAlterer.
 *
 * @group ocs_ai
 */
class CarFormAltererTest extends UnitTestCase {

  /**
   * Tests that the car description is generated and saved on form submission.
   */
  public function testCarDescriptionGenerationOnFormSubmit(): void {
    $car = $this->createMock(Car::class);

    $car->method('getBrand')->willReturn('Honda');
    $car->method('getModel')->willReturn('Civic');
    $car->method('getBodyType')->willReturn('Hatchback');
    $car->method('getColor')->willReturn('Blue');
    $car->method('getYear')->willReturn(2018);
    $car->method('getTransmissionType')->willReturn('Manual');
    $car->method('getFuelType')->willReturn('Diesel');
    $car->method('getKilometrage')->willReturn(30000);
    $car->method('getPrice')->willReturn('USD 15000');

    $car->expects($this->once())
      ->method('set')
      ->with('description', 'Generated car description');

    $car->expects($this->once())
      ->method('save');

    $mock_ai_client = $this->createMock(AIClientInterface::class);
    $mock_ai_client->method('query')
      ->willReturn('Generated car description');

    $description_generator = $this->getMockBuilder(DescriptionGenerator::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['do'])
      ->getMock();

    $description_generator->method('do')
      ->willReturn('Generated car description');

    $plugin_manager = $this->getMockBuilder('Drupal\ocs_ai\OcsAiActionPluginManager')
      ->disableOriginalConstructor()
      ->onlyMethods(['createInstance'])
      ->getMock();

    $plugin_manager->method('createInstance')
      ->with(DescriptionGenerator::PLUGIN_ID)
      ->willReturn($description_generator);

    $car_form = $this->getMockBuilder(CarForm::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['getEntity'])
      ->getMock();

    $car_form->method('getEntity')
      ->willReturn($car);

    $car_form_alterer = new CarFormAlterer($plugin_manager);

    $form = [
      'actions' => [
        'submit' => [
          '#type' => 'submit',
          '#value' => 'Save',
          '#submit' => [],
        ],
      ],
    ];
    $form_state = new FormState();
    $form_state->setFormObject($car_form);

    $car_form_alterer->alterForm($form, $form_state);

    $this->assertNotEmpty($form['actions']['submit']['#submit']);

    foreach ($form['actions']['submit']['#submit'] as $submit_callback) {
      if (is_array($submit_callback) && $submit_callback[0] instanceof CarFormAlterer && $submit_callback[1] === 'submitForm') {
        $submit_callback($form, $form_state);
      }
    }
  }

}
