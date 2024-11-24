<?php

namespace Drupal\ocs_ai_car\FormAlterer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\ocs_ai\OcsAiActionPluginManager;
use Drupal\ocs_ai\Plugin\OcsAiAction\DescriptionGenerator;
use Drupal\ocs_car\Entity\Interface\CarInterface;
use Drupal\ocs_car\Form\CarForm;
use Drupal\ocs_form\FormAlterer\FormAltererBase;

/**
 * Class CompanyFormAlterer contains form alter for company node page.
 */
class CarFormAlterer extends FormAltererBase {

  /**
   * Constructs CarFormAlterer class.
   */
  public function __construct(
    protected OcsAiActionPluginManager $actionPluginManager
  ) {}

  /**
   * {@inheritdoc}
   */
  public function alterForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $form['actions']['submit']['#submit'][] = [$this, 'submitForm'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {
    $form_object = $form_state->getFormObject();
    if (!$form_object instanceof CarForm) {
      return;
    }

    $car = $form_object->getEntity();
    if (!$car instanceof CarInterface) {
      return;
    }

    $descriptionGenerator = $this->actionPluginManager
      ->createInstance(DescriptionGenerator::PLUGIN_ID);

    $description = $descriptionGenerator->do(['car' => $car]);
    if (empty($description)) {
      return;
    }

    $car->set('description', $description);
    $car->save();
  }

}
