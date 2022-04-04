<?php
/**
 * @file
 * Contains \Drupal\lubricantadvisor\Form\LubricantAdvisorForm.
 */
namespace Drupal\lubricantadvisor\Form;
use \SoapClient;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\Element\EntityAutocomplete;

class LubricantAdvisorForm2 extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lubricantfinder_form2';
  }

  // Build a form by declaring attributes


 public function bannerTwigHtml(){
	 $block = \Drupal\block\Entity\Block::load('views_block__lubricant_finder_header_image_block_1');
	if ($block) {
	  $build['my_block']  = \Drupal::entityTypeManager()
	  ->getViewBuilder('block')
	  ->view($block); 
	}
	echo '<pre>';var_dump($build['my_block']);echo '</pre>';
	return $build;
 }


  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached']['library'][] = 'lubricantadvisor/lubricant_js';  

	
	
	/*$category_result =[];
	$config = $this->config('lubricantadvisor.settings');	
	$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
	$username = $config->get('username');
	$password = $config->get('password');
	
	
	$soapClient = new SoapClient(
	   $olyslagerApiWsdl,
	   array(
		  'trace' => TRUE, 
		  'soap_version' => SOAP_1_2
	   )
	);

	//Set the parameters for the soap call
	$parameters = array(
	   "UserName"=>$username,
	   "Password"=>$password,
	   "LanguageISO3"=>"eng"
	);
	
	$response = $soapClient->GetCategoryList($parameters);
	$xml = simplexml_load_string($response->GetCategoryListResult->any);
	
	if ($xml === false) {
	   print "\nFailed loading XML: ";
	   foreach(libxml_get_errors() as $error) {
		  echo "\n", $error->message;
	   }
	} else {

	   //If the first node == status then there is no data and someting wrong with request. See the result code and result description for more information.
	   if ($xml->getName() == "status"){

		  //Print the exception
		 // print "\nWeb service result: ".$xml->resultdescription." (resultcode: ".$xml->resultcode.")\n";
	   }
	   else {

		 // print "\nWeb service result: ".$xml->status->resultdescription." (resultcode: ".$xml->status->resultcode.")\n";

		  //Print the number of records returned by the web service
		  //print "\nNumber of records: ".$xml->ctdata->resultset['numrecords']."\n\n";

		  //Print all received records returned by the web service
		  foreach($xml->ctdata->resultset->Categories as $category) {
			 //Here is the data from this webservice request
			 print "id: ".$category['id']."; category: ".$category['result'].";\n";
			 
			 $category_result[] = array("id"=>(int)$category['id'], "category"=>$category['result']);
		  }
	   }
	}
	
	echo '<pre>';print_r($category_result);echo '</pre>';
	
	*/
	
	
	$config = $this->config('lubricantadvisor.settings');
	$Cars_category = $config->get('Cars');
	$vans = $config->get('Light_commercial_vehicles_(<75t)');
	$motorcycles = $config->get('Motorcycles_Mopeds_ATV/UTV');
	$Commercial = $config->get('Trucks_and_Buses_(>75t)');
	$Agricultural  = $config->get('Agricultural_Equipment');
	$Off_Highway  = $config->get('Construction_Mining_and_Materials_Handling_Equipment');
	$marine  = $config->get('Marine');
	
	$form['#prefix'] = "	
	
	<div class='brand-information'>    
	  <div class='container'>
      <div class='row'>
        <div class='col-md-11 offset-md-1'>
		 <div class='page-title'>
              <h1 data-aos='fade-up'>Lubricant Advisor</h1>
              <nav aria-label='breadcrumb' data-aos='fade-up'>
                <ol class='breadcrumb'>
                  <li class='breadcrumb-item'><a href='#'>Home</a></li>
                  <li class='breadcrumb-item active' aria-current='page'>
                    Lubricant Advisor
                  </li>
                </ol>
              </nav>
            </div>
		<div class='libricant-finder'>
          <h1>Choose a Sector</h1>
          <div class='iconBox d-lg-flex justify-content-lg-between align-items-center' id='iconBox'>
            <div class='sectorIcon'>
              <div class='sectorIconImage Cars'>
                <a onclick='renderMakeDropDown('category/".$Cars_category."','".$Cars_category."'); ' href='#' data-id='".$Cars_category."' data-path='category/".$Cars_category."'>
                    <div class='icon'>
                      <img alt='Cars' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-car.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$Cars_category."','".$Cars_category."'); ' href='#' data-id='".$Cars_category."' data-path='category/".$Cars_category."' >Cars</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Vans'>
                <a onclick='renderMakeDropDown('category/".$vans."','".$vans."'); ' href='#'  data-id='".$vans."' data-path='category/".$vans."'>
                    <div class='icon'>
                      <img alt='Vans' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-van.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$vans."','".$vans."'); ' href='#' data-id='".$vans."' data-path='category/".$vans."'>Vans</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Motorcycles'>
                <a onclick='renderMakeDropDown('category/".$motorcycles."','".$motorcycles."'); ' href='#'  data-id='".$motorcycles."' data-path='category/".$motorcycles."'>
                    <div class='icon'>
                      <img alt='Motorcycles' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-motorcycle.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$motorcycles."','".$motorcycles."'); ' href='#'  data-id='".$motorcycles."' data-path='category/".$motorcycles."'>Motorcycles</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Commercial'>
                <a onclick='renderMakeDropDown('category/".$Commercial."','".$Commercial."'); ' href='#'  data-id='".$Commercial."' data-path='category/".$Commercial."'>
                    <div class='icon'>
                      <img alt='Commercial' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-commercial.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$Commercial."','".$Commercial."'); ' href='#' data-id='".$Commercial."' data-path='category/".$Commercial."'>Commercial</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Agricultural'>
                <a onclick='renderMakeDropDown('category/".$Agricultural."','".$Agricultural."'); ' href='#' data-id='".$Agricultural."' data-path='category/".$Agricultural."'>
                    <div class='icon'>
                      <img alt='Agricultural' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-agricultural.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$Agricultural."','".$Agricultural."'); ' href='#' data-id='".$Agricultural."' data-path='category/".$Agricultural."'>Agricultural</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Off-Highway'>
                <a onclick='renderMakeDropDown('category/".$Off_Highway."','".$Off_Highway."'); ' href='#' data-id='".$Off_Highway."' data-path='category/".$Off_Highway."'>
                    <div class='icon'>
                      <img alt='Off-Highway' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-highway.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$Off_Highway."','".$Off_Highway."'); ' href='#' data-id='".$Off_Highway."' data-path='category/".$Off_Highway."'>Off-Highway</a>
            </div>
            <div class='sectorIcon'>
              <div class='sectorIconImage Industrial'>
                <a onclick='renderMakeDropDown('category/".$marine."','".$marine."');' href='#' data-id='".$marine."' data-path='category/".$marine."'>
                    <div class='icon'>
                      <img alt='Industrial' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/icon-marine.png' . "'>
                    </div>
                </a>
              </div>
              <a class='sectorIconText' onclick='renderMakeDropDown('category/".$marine."','".$marine."');' href='#' data-id='".$marine."' data-path='category/".$marine."'>Marine</a>
            </div>
          </div>
        </div>
      </div>
	  </div>
    </div>
   </div>";
    $form['q'] = array(
      '#type' => 'textfield',
      '#title' => ('Keyword Search'),
      '#placeholder' => t('Keyword Search'),
	  '#autocomplete_route_name' => 'lubricantadvisor.autocomplete',
      '#default_value' => isset($_GET['q']) ? $_GET['q'] : '',
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#prefix' => '<div class="modelSearchContainer facetDropdown"><div id="modelSearchInput" class="modelSearchBlock">',
      '#suffix' => '<div id="lubadvisorautocomp" class="autocomp" ></div></div>',
    );
    $form['familygroup'] = array(
      '#type' => 'select',
      '#options' => array(
        '' => t('All'),
        'Cars' => t('Cars'),
        'Vans' => t('Vans'),
        'Motorcycles' => t('Motorcycles'),
        'Commercial' => t('Commercial'),
        'Agricultural' => t('Agricultural'),
        'Off-Highway' => t('Off-Highway'),
        'Marine' => t('Marine'),
      ),
      '#attributes' => array('id' => 'familygroup', 'style' => 'display: none;'),
      '#prefix' => '<div id="modelSearchSelect" class="modelSearchBlock">',
      '#suffix' => '</div></div>',
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#button_type' => 'primary',
      '#attributes' => array(
        'class' => array('submit_overall_search'), 'style' => 'display: none;',
      ),
      '#prefix' => '<div id="modelSearchButton" class="modelSearchBlock">',
      '#suffix' => '</div>',
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
    $triggeringElement = $form_state->getTriggeringElement();
    console . log($triggeringElement);
    if ($form_state->getValue('family_group') == '--Select--') {
      $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/browse?token=ldWMYvB1ryWu";
    } else {
      $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/browse/"+$form_state->getValue('family_group')+"?token=ldWMYvB1ryWu";
    }
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