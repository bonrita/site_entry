<?php

namespace Drupal\site_entry\Form;

use Drupal\site_entry\LoginClient;
use Drupal\Core\Database\Log;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExternalLoginSettingsForm.
 *
 * @package Drupal\site_entry\Form
 */
class ExternalLoginSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'eloqua_general_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['site_entry.external_login'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $eloqua_settings = $this->config('site_entry.external_login');

    $form['credentials'] = [
      '#type' => 'fieldset',
      '#title' => t('Login credentials'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['credentials']['application_id'] = [
      '#type' => 'textfield',
      '#title' => t('Application ID'),
      '#default_value' => $eloqua_settings->get('connection_settings.application_id'),
      '#required' => TRUE,
      '#description' => $this->t('For example: com.br.web'),
    ];

    $form['credentials']['end_point'] = [
      '#type' => 'textfield',
      '#title' => t('Endpoint'),
      '#default_value' => $eloqua_settings->get('connection_settings.end_point'),
      '#required' => TRUE,
      '#description' => $this->t('For example: jwt-service/v1/auth'),
    ];

    $form['credentials']['host'] = [
      '#type' => 'textfield',
      '#title' => t('Host'),
      '#default_value' => $eloqua_settings->get('connection_settings.host'),
      '#required' => TRUE,
      '#description' => $this->t('For example: brambles-jwt-service.bramblesvnet.p.azurewebsites.net'),
    ];

    $form['links'] = [
      '#type' => 'fieldset',
      '#title' => t('Login links'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $form['links']['forgot_password'] = [
      '#type' => 'textfield',
      '#title' => t('Forgot password link'),
      '#default_value' => $eloqua_settings->get('connection_settings.forgot_password'),
    ];

    $form['links']['login_help'] = [
      '#type' => 'textfield',
      '#title' => t('Login help link'),
      '#default_value' => $eloqua_settings->get('connection_settings.login_help'),
    ];

    $form['links']['learn_br'] = [
      '#type' => 'textfield',
      '#title' => t('Learn about my br link'),
      '#default_value' => $eloqua_settings->get('connection_settings.learn_br'),
    ];

    $form['links']['goto_br'] = [
      '#type' => 'textfield',
      '#title' => t('Go to mybr '),
      '#default_value' => $eloqua_settings->get('connection_settings.goto_br'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $eloqua_settings = $this->config('site_entry.external_login');

    $eloqua_settings
      ->set('connection_settings.application_id', trim($form_state->getValue('application_id'), '/ '))
      ->set('connection_settings.end_point', trim($form_state->getValue('end_point'), '/ '))
      ->set('connection_settings.host', trim($form_state->getValue('host'), '/ '))
      ->set('connection_settings.forgot_password', trim($form_state->getValue('forgot_password'), '/ '))
      ->set('connection_settings.login_help', trim($form_state->getValue('login_help'), '/ '))
      ->set('connection_settings.learn_br', trim($form_state->getValue('learn_br'), '/ '))
      ->set('connection_settings.goto_br', trim($form_state->getValue('goto_br'), '/ '))
      ->save();
  }

}
