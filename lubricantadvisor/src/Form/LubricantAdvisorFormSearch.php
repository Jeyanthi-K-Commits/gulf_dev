<?php
/**
 * @file
 * Contains \Drupal\lubricantadvisor\Form\lubricantadvisorForm.
 */
namespace Drupal\lubricantadvisor\Form;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class LubricantAdvisorFormSearch extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lubricantadvisor_form_search';
  }

  // Build a form by declaring attributes

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'lubricantadvisor/lubricant_js';
      $form['family_group'] = array(
      '#type' => 'textfield',
      '#title' => $this
      ->t('Keyword Search'),
      '#default_value' => '',
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
      '#ajax' => ['callback' => [$this, 'renderJson'],
        'method' => 'html',
        'wrapper' => 'states-to-update',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    );
    return $form;
  }

//Validating form

  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  //Trigger event

  //submitting form
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }
//Functions

  public function renderJson(array &$element, FormStateInterface $form_state) {
    echo "<pre>";
    print_r( $form_state );
    die();
    $triggeringElement = $form_state->getTriggeringElement();
    //console . log($triggeringElement);
   /* if ($form_state->getValue('family_group') == '--Select--') {
      $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/search?token=ldWMYvB1ryWu";
    } else {
      $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/search/"+$form_state->getValue('family_group')+"?token=ldWMYvB1ryWu";
    }*/
    $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/search/"+$form_state->getValue('family_group')+"?token=ldWMYvB1ryWu";
    // $value = $triggeringElement['#value'];
    // $states = $this->getStatesByCountry($value);
    // $wrapper_id = $triggeringElement["#ajax"]["wrapper"];
    // $renderedField = '';
    // foreach ($states as $key => $value) {
    //   $renderedField .= "<option value='".$key."'>".$value."</option>";
    // }
    $response = new AjaxResponse();
    $response->addCommand(new HtmlCommand("#" . $wrapper_id, $url));
    return $response;
  }

  public function getStatesByCountry($default_country) {
    //add you logic return states by country
    return $states;
  }

}  