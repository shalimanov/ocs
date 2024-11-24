<?php

namespace Drupal\ocs_form\FormAlterer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class FormAltererBase describes base form alterer class.
 */
abstract class FormAltererBase implements FormAltererInterface {

  /**
   * {@inheritdoc}
   */
  public function validateForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void {

  }

}
