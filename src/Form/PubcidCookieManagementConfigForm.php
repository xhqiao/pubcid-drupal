<?php
/**
 * Created by IntelliJ IDEA.
 * User: xqiao
 * Date: 4/24/19
 * Time: 1:21 PM
 */

namespace Drupal\pubcid_cookie_management\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides settings for eu_cookie_compliance module.
 */
class PubcidCookieManagementConfigForm extends ConfigFormBase
{


  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames()
  {
    return ['pubcid_cookie_management.settings'];
  }


  /**
   * Returns a unique string identifying the form.
   *
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId()
  {
    return 'pubcid_cookie_management_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('pubcid_cookie_management.settings');

    //Pubcid Cookie Extender Form
    $form['pubcid_cookie_extender'] = [
      '#type' => 'details',
      '#title' => t('Pubcid Cookie Extender Configuration'),
      '#open' => TRUE,
    ];

    //Cookie Name
    if ($config->get('cookie_name') == '') {
      $cookie_name_default = 'example: _pubcid';
    } else {
      $cookie_name_default = $config->get('cookie_name');
    }
    $form['pubcid_cookie_extender']['cookie_name'] = [

      '#type' => 'textfield',
      '#title' => t('Cookie Name'),
      '#default_value' => $cookie_name_default,
      '#attributes' => array(
        'onblur' => "if (this.value == '') {this.value = 'example: _pubcid'}",
        'onfocus' => "if (this.value == 'example: _pubcid') {this.value = ''}"
      , ),
    ];

    //Maximum Age
    $options = [7, 30, 90, 365];
    $form['pubcid_cookie_extender']['max_age'] = [
      '#type' => 'select',
      '#title' => t('Max Age'),
      '#default_value' => $config->get('max_age'),
      '#options' => array_combine($options, $options),
      '#description' => t('Max Age of the cookie in days'),
    ];

    //Cookie Path
    $form['pubcid_cookie_extender']['cookie_path'] = [
      '#type' => 'textfield',
      '#title' => t('Cookie Path'),
      '#default_value' => $config->get('cookie_path'),
      '#description' => t('Path of the cookie'),
    ];

    //The Cookie Consent Function
    if ($config->get('consent_function') == '') {
      $consent_function_default = 'example: cn_cookie_accept';
    } else {
      $consent_function_default = $config->get('consent_function');
    }
    $form['pubcid_cookie_extender']['consent_function'] = [
      '#type' => 'textfield',
      '#title' => t('Consent Function'),
      '#default_value' => $consent_function_default,
      '#attributes' => array(
        'onblur' => "if (this.value == '') {this.value = 'example: cn_cookie_accept'}",
        'onfocus' => "if (this.value == 'example: cn_cookie_accept') {this.value = ''}"
      , ),
      '#description' => t('If specified, then cookie is not updated unless the function returns true.'),
    ];

    //Cookie Value Generation Function
    if ($config->get('generate_function') == '') {
      $generate_function_default = 'example: dp_generate_uuid';
    } else {
      $generate_function_default = $config->get('generate_function');
    }
    $form['pubcid_cookie_extender']['generate_function'] = [
      '#type' => 'textfield',
      '#title' => t('Generate Function'),
      '#default_value' => $generate_function_default,
      '#attributes' => array(
        'onblur' => "if (this.value == '') {this.value = 'example: Drupal\pubcid_cookie_management\EventSubscriber\pubcid_value_generation'}",
        'onfocus' => "if (this.value == 'example: Drupal\pubcid_cookie_management\EventSubscriber\pubcid_value_generation') {this.value = ''}"
      , ),
      '#description' => t('If specified, then cookie is automatically generated using the value returned by the function.
                           Enter Drupal\pubcid_cookie_management\EventSubscriber\pubcid_value_generation if you want to use the default one'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $this->config('pubcid_cookie_management.settings')
      ->set('cookie_name', $form_state->getValue('cookie_name'))
      ->set('max_age', $form_state->getValue('max_age'))
      ->set('cookie_path', $form_state->getValue('cookie_path'))
      ->set('consent_function', $form_state->getValue('consent_function'))
      ->set('generate_function', $form_state->getValue('generate_function'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
