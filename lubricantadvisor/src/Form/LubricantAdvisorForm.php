<?php
/**
 * @file
 * Contains \Drupal\lubricantadvisor\Form\LubricantAdvisorForm.
 */
namespace Drupal\lubricantadvisor\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class LubricantAdvisorForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lubricantfinder_form';
  }

  // Build a form by declaring attributes

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'lubricantadvisor/lubricant_js';
    $form['#prefix'] = '<div class="container"><div class="row"><div class="col-md-11 offset-md-1"><div class="lubricantfinder_page_or"><b>OR</b></div><div class="ajax-dropdowns dropDown">';
    $form['#suffix'] = "<img src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/preloader.gif' . "' id='loading' style='display:none' />
				<div id='sub_filters_search' class='text-center'>
                  <a class='btn-search submit_filters_search'>Search</a>                    
                </div><table id='dataList' class='table' data-page-length='1' cellspacing='0' width='100%'>
</table></div></div></div></div>";


    $form['manufacturer'] = array(
      '#type' => 'select',
      '#title' => 'Choose a Make',
      '#options' => array('' => '---Please Select---'),
    );
	
	$form['model'] = array(
      '#type' => 'select',
      '#title' => 'Choose a Model',
      '#options' => array('' => '---Please Select---'),
	  '#disabled' => TRUE,
    );
	
	$form['type'] = array(
      '#type' => 'select',
      '#title' => 'Choose a Type',
      '#options' => array('' => '---Please Select---'),
	  '#disabled' => TRUE,
    );
	
	$form['category_id'] = array(
	  '#type' => 'hidden',
	  '#value' => '',
	);
	
	$form['make_id'] = array(
		  '#type' => 'hidden',
		  '#value' => '',
		);
	$form['model_id'] = array(
	  '#type' => 'hidden',
	  '#value' => '',
	);
	
	$form['type_id'] = array(
	  '#type' => 'hidden',
	  '#value' => '',
	);
	
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
	  '#attributes' => array(
        'class' => array('btn-search'),
      ),
	  '#prefix' => '<div class="text-center mb-4">',
      '#suffix' => '</div>',
    );
			
    return $form;
  }

  //submitting form
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . $value);
    }
  }

}