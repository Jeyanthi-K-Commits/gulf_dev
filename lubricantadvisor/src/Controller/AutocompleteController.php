<?php

namespace Drupal\lubricantadvisor\Controller;
use \SoapClient;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request) {
    $results = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
		$typed_string = Tags::explode($input);
		//$typed_string = Unicode::strtolower(array_pop($typed_string));
		// @todo: Apply logic for generating results based on typed_string and other
		// arguments passed.
		
		//$config = \Drupal::config('lubricantadvisor.settings');
		
		
		$config = $this->config('lubricantadvisor.settings');
   
		//echo 'olyslagerApiWsdl==='.$config->get('olyslagerApiWsdl');		
		//echo 'username==='.$config->get('username'); 
		//echo 'password==='.$config->get('password'); 
		//die();
		
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
			"LanguageISO3"=>"eng",
			"SearchText"=>$input,
			"CategoryID"=>""
		);
	  
	    $response = $soapClient->GetTypeListFromInstantSearch($parameters);
		$xml = simplexml_load_string($response->GetTypeListFromInstantSearchResult->any);
		
		$array =$type_array = [];
		
		if ($xml === false) {
		   return new JsonResponse($results);
		   echo "\nFailed loading XML: ";
		   foreach(libxml_get_errors() as $error) {
			  echo "\n", $error->message;
		   }
		} else {

		   //If the first node == status then there is no data and someting wrong with request. See the result code and result description for more information.
		   if ($xml->getName() == "status"){

			  //Print the exception
			  //print "\nWeb service result: ".$xml->resultdescription." (resultcode: ".$xml->resultcode.")\n";
		   }
		   else {

			  //print "\nWeb service result: ".$xml->status->resultdescription." (resultcode: ".$xml->status->resultcode.")\n";

			  //Print the number of records returned by the web service
			  //print "\nNumber of records: ".$xml->ctdata->resultset['numrecords']."\n\n";

			  //Print all received records returned by the web service
			  
			  
			  /*foreach($xml->ctdata->resultset->Types as $type) {
				 //Here is the data from this webservice request
				// print "id: ".$type['id']."; type: ".$type['result'].";\n";
				 
				 $results[] = [
					'value' => $type['result'] . '(' . $type['id'].')',
					'label' => $type['result'] . ' ' . $type['id'],
					'url' => 'recommendation/'.$type['id'],
				];
			  }*/
			  
			  
			  $array = json_decode(json_encode((array) $xml), 1);
			  $type_array = $array['ctdata']['resultset']['Types'] ; 
			  $count = $array['ctdata']['resultset']['@attributes']['numrecords'];
			  
			  if($count >1){
				$type_array = $array['ctdata']['resultset']['Types'] ;  
			  }else if($count ==1){
				  $type_array[] = $array['ctdata']['resultset']['Types'] ;
			  }else {
				  $type_array =[];
			  }
			  
			  //echo '<pre>'; print_r($array);echo '</pre>';
			  if (!empty($type_array) && count($type_array) > 0) {
					foreach ($type_array as $key => $value) {
					  foreach($value as $k => $val){
						  //echo '<pre>'; print_r($val);echo '</pre>';
						  $results[] = [
								'value' => $val['result'] . '(' . $val['id'].')', 
								'label' => $val['result'] ,
								'url' => 'recommendation?typeid='.$val['id'],
							];
					  }
					}
			  }else {
				  // array is empty  - No Results Found 
				  $results[] = [
								'value' => '0', 
								'label' => 'No Results Found',
								'url' => '',
							];
			  }
			  
			  
			  
			  
			  
			  
			  //die();
			  
			
			  
		   }
		}
	  
		/*$count =10;$field_name='test';

		for ($i = 0; $i < $count; $i++) {
			$results[] = [
				'value' => $field_name . '_' . $i . '(' . $i . ')',
				'label' => $field_name . ' ' . $i,
			];
		}*/
		
    }else {
		return new JsonResponse($results);
	}
	
	//echo 'q==='.$input;
	//echo '<pre>'; print_r($results);echo '</pre>';
	//die();

    return new JsonResponse($results);
  }

}