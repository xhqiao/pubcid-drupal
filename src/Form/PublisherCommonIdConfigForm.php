<?php
namespace Drupal\publisher_common_id\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Provides settings for Publisher Common ID module
 */
class PublisherCommonIdConfigForm extends ConfigFormBase implements FormInterface
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
        return ['publisher_common_id.settings'];
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
        return 'publisher_common_id_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('publisher_common_id.settings');

        //Publisher Common Id Form
        $form['publisher_common_id'] = [
            '#type' => 'details',
            '#title' => t('Publisher Common ID Configuration'),
            '#open' => TRUE,
        ];

        //Cookie Name
        $form['publisher_common_id']['cookie_name'] = [

            '#type' => 'textfield',
            '#title' => t('Cookie Name'),
            '#placeholder' => t('example: _pubcid'),
            '#description' => t('The default cookie name is _pubcid'),
        ];

        //Maximum Age
        $options = [7, 30, 90, 365];
        $form['publisher_common_id']['max_age'] = [
            '#type' => 'select',
            '#title' => t('Max Age'),
            '#default_value' => $config->get('max_age'),
            '#options' => array_combine($options, $options),
            '#description' => t('Max Age of the cookie in days'),
        ];

        //Cookie Path
        $form['publisher_common_id']['cookie_domain'] = [
            '#type' => 'textfield',
            '#title' => t('Cookie Domain'),
            '#default_value' => $config->get('cookie_domain'),
            '#description' => t('Cookie Domain'),
        ];

        //The Cookie Consent Function
        $form['publisher_common_id']['consent_function'] = [
            '#type' => 'textfield',
            '#title' => t('Consent Function'),
            '#placeholder' => t('example: cn_cookies_accepted'),
            '#description' => t('If specified, then cookie is not updated unless the function returns true.'),
        ];

        //Cookie Value Generation Function
        $form['publisher_common_id']['generate_function'] = [
            '#type' => 'textfield',
            '#title' => t('Generate Function'),
            '#placeholder' => t('example: dp_generate_uuid'),
            '#description' => t('If specified, then cookie is automatically generated using the value returned by the function.
                           Enter Drupal\publisher_common_id\Controller\pubcid_value_generation if you want to use the default one'),
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        $consent_function = $form_state->getValue('consent_function');
        $uuid_generation =  $form_state->getValue('generate_function');

        if(!empty($consent_function) && !is_callable($consent_function)) {
            $form_state->setErrorByName('name', $this->t('Consent Function %name is not callable', ['%name' => $consent_function]));
        }

        if(!empty($uuid_generation) && $uuid_generation != 'Drupal\publisher_common_id\Controller\pubcid_value_generation' && !is_callable($uuid_generation)) {
            $form_state->setErrorByName('name', $this->t('Generate Function %name is not callable', ['%name' => $uuid_generation]));
        }
    }


    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('publisher_common_id.settings')
            ->set('cookie_name', ($form_state->getValue('cookie_name')))
            ->set('max_age', $form_state->getValue('max_age'))
            ->set('cookie_domain', $form_state->getValue('cookie_domain'))
            ->set('consent_function', $form_state->getValue('consent_function'))
            ->set('generate_function', $form_state->getValue('generate_function'))
            ->save();

        parent::submitForm($form, $form_state);
    }
}


