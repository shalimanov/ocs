<?php

namespace Drupal\ocs_form\FormAlterer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface FormAltererInterface describes form alterer interface.
 */
interface FormAltererInterface {

  /**
   * Alter a form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function alterForm(
    array &$form,
    FormStateInterface $form_state,
  ): void;

  /**
   * Validate a form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateForm(
    array &$form,
    FormStateInterface $form_state,
  ): void;

  /**
   * Submit a form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function submitForm(
    array &$form,
    FormStateInterface $form_state,
  ): void;

}
