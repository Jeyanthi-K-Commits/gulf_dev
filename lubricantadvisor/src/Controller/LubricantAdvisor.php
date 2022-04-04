<?php
namespace Drupal\lubricantadvisor\Controller;
use \SoapClient;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class LubricantAdvisor extends ControllerBase {
  // public $mailcontent='';
  // public $attr_formail='';
  /**   * The form builder.   *   * @var \Drupal\Core\Form\FormBuilder   */
  protected $formBuilder;

  /**   * The ModalFormExampleController constructor.   *   * @param \Drupal\Core\Form\FormBuilder $formBuilder   *   The form builder.   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**   * {@inheritdoc}   *   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   *   The Drupal service container.   *   * @return static   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  public function lubricantadvisorcategoryinfo($category_id = ''){
	//$config = $this->config('lubricantadvisor.settings');
	$config = \Drupal::config('lubricantadvisor.settings');
	$Cars = $config->get('Cars');	
	//echo 'category_id==='.$category_id; 

    $olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
	$username = $config->get('username');
	$password = $config->get('password');
	$CategoryID =$category_id;
	//Set the soap client and enable tracing for debugging
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
	   "CategoryID"=>$CategoryID,
	);
	
	$response = $soapClient->GetMakeList($parameters);
	//Load the xml with an xml parser
	$xml = simplexml_load_string($response->GetMakeListResult->any);

	$array = $res = []; $count = 0;
	
	//Check if parsing succeed
	if ($xml === false) {
	   return new JsonResponse(['category' => $res, 'method' => 'POST', 'status' => 100]);
	   echo "\nFailed loading XML: ";
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

		  //print "\nWeb service result: ".$xml->status->resultdescription." (resultcode: ".$xml->status->resultcode.")\n";

		  //Print the number of records returned by the web service
		  //print "\nNumber of records: ".$xml->ctdata->resultset['numrecords']."\n\n";

		  //Print all received records returned by the web service
		  
		  $array = json_decode(json_encode((array) $xml), 1);
		  $count = $array['ctdata']['resultset']['@attributes']['numrecords'];

		  $res = $array['ctdata']['resultset']['Makes'] ;
		  
		  if($count >1){
			$res = $array['ctdata']['resultset']['Makes'] ;  
		  }else if($count ==1){
			  $res[] = $array['ctdata']['resultset']['Makes'] ;
		  }else {
			  $res =[];
		  }
		  		  
		  /*foreach($xml->ctdata->resultset->Makes as $make) {
			 //Here is the data from this webservice request
			// print "id: ".$make['id']."; make: ".$make['result'].";\n";
			$id= $make['id']; $make = $make['result'];
			$results[] = array("id"=>(array)$id, "name"=>(array)$make); 
			
		  }*/
	   }
	}
	
	


	
	//echo "<pre>";
    //print_r($array['ctdata']['resultset']['Makes']);
    //echo "</pre>";	
	//echo new JsonResponse($res);
    //exit;
    
	
    return new JsonResponse(['category' => $res, 'method' => 'POST', 'status' => 200]);
	
  }
  
  
  public function lubricantadvisormodelinfo($make_id = ''){
	$config = $this->config('lubricantadvisor.settings');
	$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
	$username = $config->get('username');
	$password = $config->get('password');
	
	$MakeID = $make_id;
	
	//Set the soap client and enable tracing for debugging
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
	   "MakeID"=>$MakeID,
	);
	//Call the soap service and store the response.
	$response = $soapClient->GetModelList($parameters);
	//Load the xml with an xml parser
	$xml = simplexml_load_string($response->GetModelListResult->any);
	//Check if parsing succeed
	$array = $model_array = [];$count =0;
	if ($xml === false) {
	   return new JsonResponse(['model' => $model_array, 'method' => 'POST', 'status' => 100]);
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
		  //$count = $xml->ctdata->resultset['numrecords'];
		  //Print all received records returned by the web service
		  $array = json_decode(json_encode((array) $xml), 1);
		  $count = $array['ctdata']['resultset']['@attributes']['numrecords'];
		 // echo 'count=='.$count;
		  if($count >1){
			$model_array = $array['ctdata']['resultset']['Models'] ;  
		  }else if($count ==1){
			  $model_array[] = $array['ctdata']['resultset']['Models'] ;
		  }else {
			  $model_array =[];
		  }
		  
	   }
	}
	
	
	/*echo "<pre>";
	print_r($array);
    //print_r($array['ctdata']['resultset']['Models']);
    echo "</pre>";*/
	
	return new JsonResponse(['model' => $model_array,  'method' => 'POST', 'status' => 200]);
	
  }
  
  public function lubricantadvisortypeinfo($model_id=''){
	$config = $this->config('lubricantadvisor.settings');
	$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
	$username = $config->get('username');
	$password = $config->get('password');
	
	$ModelID = $model_id;
	
	//Set the soap client and enable tracing for debugging
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
	   "ModelID"=>$ModelID,
	);
	
	//Call the soap service and store the response.
	$response = $soapClient->GetTypeList($parameters);
	//Load the xml with an xml parser
	$xml = simplexml_load_string($response->GetTypeListResult->any);
	$array = $type_array = []; $count =0;
	//Check if parsing succeed
	if ($xml === false) {
	   return new JsonResponse(['type' => $type_array, 'method' => 'POST', 'status' => 100]);
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
		 
		  $array = json_decode(json_encode((array) $xml), 1);
		  $count = $array['ctdata']['resultset']['@attributes']['numrecords'];
		   if($count >1){
			$type_array = $array['ctdata']['resultset']['Types'] ;  
		  }else if($count ==1){
			  $type_array[] = $array['ctdata']['resultset']['Types'] ;
		  }else {
			  $type_array =[];
		  }
		 
	   }
	}
	
	return new JsonResponse(['type' => $type_array,'count'=>$count, 'method' => 'POST', 'status' => 200]);
	
  }




  public function lubricantadvisorinfo($field_data = '', $field_data1 = '', $field_data2 = '', $field_data3 = '', $field_data4 = '', $field_data5 = '', $field_data6 = '', $field_data7 = '') {
    
	
	if ($field_data != '' && $field_data != 'json') {
      $parameter = "/" . $field_data;
    }
    if ($field_data1 != '' && $field_data1 != 'json') {
      $parameter .= "/" . $field_data1;
    }
    if ($field_data2 != '' && $field_data2 != 'json') {
      $parameter .= "/" . $field_data2;
    }
    if ($field_data3 != '' && $field_data3 != 'json') {
      $parameter .= "/" . $field_data3;
    }
    if ($field_data4 != '' && $field_data4 != 'json') {
      $parameter .= "/" . $field_data4;
    }
    if ($field_data5 != '' && $field_data5 != 'json') {
      $parameter .= "/" . $field_data5;
    }
    if ($field_data6 != '' && $field_data6 != 'json') {
      $parameter .= "/" . $field_data6;
    }
    if ($field_data7 != '' && $field_data7 != 'json') {
      $parameter .= "/" . $field_data7;
    }
	
	
	
    $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/browse" . $parameter . "?token=ldWMYvB1ryWu";
    $client = \Drupal::httpClient();
    $request = $client->get($url, [
      'headers' => [
        'Content-Type' => 'application/xml',
      ],
      'timeout' => 10000,
    ]);
    $json_string = (string) $request->getBody();
    $fileContents = str_replace(array("\n", "\r", "\t"), '', $json_string);

    $fileContents = trim(str_replace('"', "'", $fileContents));

    $simpleXml = simplexml_load_string($fileContents);
    $attr_item_Data = json_decode(json_encode((array) $simpleXml), TRUE);
    foreach ($simpleXml->children() as $child) {
      foreach ($child->children() as $key => $value) {
        foreach ($value->attributes() as $key1 => $value1) {
          $attr_Data = json_decode(json_encode((array) $value), TRUE);
          if ($key1 == 'href') {
            $attr_values[$attr_Data[0]] = $attr_Data['@attributes'];
          }
        }
        foreach ($value->children() as $keys => $values) {
          foreach ($values->attributes() as $key2 => $value2) {
            $attr_Data = json_decode(json_encode((array) $values), TRUE);
            if ($key2 == 'href') {
              $attr_values[$attr_Data[0]] = $attr_Data['@attrixzdbutes'];
            }
          }
        }
      }
    }
    foreach ($attr_item_Data as $key => $value) {
      $json_attr_values['href'] = $value['@attributes']['href'];
      if (isset($value['parent'])) {
        if (isset($value['parent']['@attributes'])) {
          $record_no = 0;
          if (count($value['parent']['item']) > 1) {
            foreach ($value['parent']['item'] as $item_key => $item_value) {
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['name'] = $item_value;
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = false;
              if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
                $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = true;
              }
              $record_no++;
            }
          } else {
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['name'] = $value['parent']['item'];
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = false;
            if (strrpos($value['@attributes']['href'], $attr_values[$value['parent']['item']]['href']) !== false) {
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = true;
            }
          }
        } else {
          foreach ($value['parent'] as $parent_key => $parent_value) {
            $record_no = 0;
            if (count($parent_value['item']) > 1) {
              foreach ($parent_value['item'] as $item_key => $item_value) {
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['name'] = $item_value;
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = false;
                if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
                  $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = true;
                }
                $record_no++;
              }
            } else {
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['name'] = $parent_value['item'];
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = false;
              if (strrpos($value['@attributes']['href'], $attr_values[$parent_value['item']]['href']) !== false) {
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = true;
              }
            }
          }
        }
      }
      if (isset($value['item'])) {
        if (count($value['item']) == 1) {
          $record_no = 0;
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['name'] = $value['item'];
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['href'] = $attr_values[$value['item']]['href'];
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = false;
          if (strrpos($value['@attributes']['href'], $attr_values[$value['item']]['href']) !== false) {
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = true;
          }
        } else {
          $record_no = 0;
          foreach ($value['item'] as $item_key => $item_value) {
            //print_r($item_value);
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['name'] = $item_value;
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = false;
            if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
              $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = true;
            }
            $record_no++;
          }
        }
      }
      if (isset($value['equipment'])) {
        if (isset($value['equipment']['@attributes'])) {
          $json_attr_values['equipment'][0]['@btid'] = $value['equipment']['@attributes']['btid'];
          $json_attr_values['equipment'][0]['@cxid'] = $value['equipment']['@attributes']['cxid'];
          $json_attr_values['equipment'][0]['@guid'] = $value['equipment']['@attributes']['guid'];
          $json_attr_values['equipment'][0]['@href'] = $value['equipment']['@attributes']['href'];
          $json_attr_values['equipment'][0]['@id'] = $value['equipment']['@attributes']['id'];
          $json_attr_values['equipment'][0]['@language'] = $value['equipment']['@attributes']['language'];
          $json_attr_values['equipment'][0]['family']['@original'] = $value['equipment']['family'];
          $json_attr_values['equipment'][0]['family']['#text'] = $value['equipment']['family'];
          $json_attr_values['equipment'][0]['familygroup']['@original'] = $value['equipment']['familygroup'];
          $json_attr_values['equipment'][0]['familygroup']['#text'] = $value['equipment']['familygroup'];
          $json_attr_values['equipment'][0]['manufacturer'] = $value['equipment']['manufacturer'];
          $json_attr_values['equipment'][0]['manufacturer_original'] = $value['equipment']['manufacturer_original'];
          $json_attr_values['equipment'][0]['model'] = $value['equipment']['model'];
          $json_attr_values['equipment'][0]['model_original'] = $value['equipment']['model_original'];
          $json_attr_values['equipment'][0]['alt_fueltype'] = $value['equipment']['alt_fueltype'];
          $json_attr_values['equipment'][0]['alt_fueltype_original'] = $value['equipment']['alt_fueltype_original'];
          $json_attr_values['equipment'][0]['series']['@original'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['series']['#text'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['displacement'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['yearfrom'] = $value['equipment']['yearfrom'];
          $json_attr_values['equipment'][0]['yearto'] = $value['equipment']['yearto'];
          $json_attr_values['equipment'][0]['display_year'] = $value['equipment']['display_year'];
          $json_attr_values['equipment'][0]['display_name_short'] = $value['equipment']['display_name_short'];
          $json_attr_values['equipment'][0]['fueltype']['@original'] = $value['equipment']['fueltype'];
          $json_attr_values['equipment'][0]['fueltype']['#text'] = $value['equipment']['fueltype'];
          $json_attr_values['equipment'][0]['display_name_long'] = $value['equipment']['display_name_long'];

        } else {
          foreach ($value['equipment'] as $equipment_key => $equipment_value) {
            $json_attr_values['equipment'][$equipment_key]['@btid'] = $equipment_value['@attributes']['btid'];
            $json_attr_values['equipment'][$equipment_key]['@cxid'] = $equipment_value['@attributes']['cxid'];
            $json_attr_values['equipment'][$equipment_key]['@guid'] = $equipment_value['@attributes']['guid'];
            $json_attr_values['equipment'][$equipment_key]['@href'] = $equipment_value['@attributes']['href'];
            $json_attr_values['equipment'][$equipment_key]['@id'] = $equipment_value['@attributes']['id'];
            $json_attr_values['equipment'][$equipment_key]['@language'] = $equipment_value['@attributes']['language'];
            $json_attr_values['equipment'][$equipment_key]['family']['@original'] = $equipment_value['family'];
            $json_attr_values['equipment'][$equipment_key]['family']['#text'] = $equipment_value['family'];
            $json_attr_values['equipment'][$equipment_key]['familygroup']['@original'] = $equipment_value['familygroup'];
            $json_attr_values['equipment'][$equipment_key]['familygroup']['#text'] = $equipment_value['familygroup'];
            $json_attr_values['equipment'][$equipment_key]['manufacturer'] = $equipment_value['manufacturer'];
            $json_attr_values['equipment'][$equipment_key]['manufacturer_original'] = $equipment_value['manufacturer_original'];
            $json_attr_values['equipment'][$equipment_key]['model'] = $equipment_value['model'];
            $json_attr_values['equipment'][$equipment_key]['model_original'] = $equipment_value['model_original'];
            $json_attr_values['equipment'][$equipment_key]['alt_fueltype'] = $equipment_value['alt_fueltype'];
            $json_attr_values['equipment'][$equipment_key]['alt_fueltype_original'] = $equipment_value['alt_fueltype_original'];
            $json_attr_values['equipment'][$equipment_key]['series']['@original'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['series']['#text'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['displacement'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['yearfrom'] = $equipment_value['yearfrom'];
            $json_attr_values['equipment'][$equipment_key]['yearto'] = $equipment_value['yearto'];
            $json_attr_values['equipment'][$equipment_key]['display_year'] = $equipment_value['display_year'];
            $json_attr_values['equipment'][$equipment_key]['display_name_short'] = $equipment_value['display_name_short'];
            $json_attr_values['equipment'][$equipment_key]['fueltype']['@original'] = $equipment_value['fueltype'];
            $json_attr_values['equipment'][$equipment_key]['fueltype']['#text'] = $equipment_value['fueltype'];
            $json_attr_values['equipment'][$equipment_key]['display_name_long'] = $equipment_value['display_name_long'];
          }
        }
      }
    }
    /*echo "<pre>";
    print_r($json_attr_values['equipment']);
    echo "</pre>";
    exit;*/
    //return array();
    return new JsonResponse(['browse' => $json_attr_values, 'method' => 'GET', 'status' => 200]);
  }
  
  
  public function typeid2recommendationinfoString(){
	  
	  $xmlstr ='<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <soap:Body>
        <TypeID2RecommendationResponse xmlns="http://olyslager-customerAPI.lubricantinformation.com">
            <TypeID2RecommendationResult>
                <ctmessage xmlns="">
                    <status>
                        <resultcode>1</resultcode>
                        <resultdescription>OK</resultdescription>
                        <server>olyslager-customerAPI.lubricantinformation.com</server>
                    </status>
                    <vehicle>
                        <make name="BMW" />
                        <model name="5 series, E60 / E61 (2003-2011)" />
                        <type name="520d DPF" />
                        <yearrange name="2007 - 2010" />
                    </vehicle>
                    <advice>
                        <brandrange id="1">
                            <component name="Engine" code="N47D20">
                                <number>1</number>
                                <apporder>1</apporder>
                                <capacities>
                                    <capacity>Capacity 5,2 litre</capacity>
                                </capacities>
                                <use name="Flexible (max)">
                                    <intervals>
                                        <interval>Change 30000 km/ 24 months</interval>
                                    </intervals>
                                    <product number="1" apporder="1" productcode="1205" name="Gulf Formula GVX SAE 5W-30" id="267514" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1_DZNs87eflgX9iboS3kjqP3w-VBvKR-T/view?usp=sharing" />
                                    <product number="2" apporder="2" productcode="1230" name="Gulf Formula CX SAE 5W-30" id="267515" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1UAjCVsgZKmgql7BN9OqvrG4PE4Y19sKv/view?usp=sharing" />
                                    <product number="3" apporder="7" productcode="1221" name="Gulf Formula ULE SAE 5W-40" id="267520" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1R1Itw0C2jaC7kOrsTA9oEeeYowYCHG1D/view?usp=sharing" />
                                </use>
                            </component>
                            <component name="Differential, rear" code="188L">
                                <number>2</number>
                                <apporder>3</apporder>
                                <capacities>
                                    <capacity>Capacity 1 litre</capacity>
                                </capacities>
                                <use name="Normal">
                                    <intervals />
                                    <product number="1" apporder="1" productcode="" name="Special Product" id="99999" temperature="" url="" packshot="" />
                                </use>
                            </component>
                            <component name="Transmission, automatic" code="ZF 6HP19 6/1">
                                <number>3</number>
                                <apporder>3</apporder>
                                <capacities>
                                    <capacity>Capacity 9-9,5 litre</capacity>
                                </capacities>
                                <use name="Normal">
                                    <intervals />
                                    <product number="1" apporder="1" productcode="" name="Special Product" id="99999" temperature="" url="" packshot="" />
                                </use>
                            </component>
                            <component name="Transmission, manual, with MTF-LT3 label" code="ZF GS6-37BZ/DZ 6/1">
                                <number>4</number>
                                <apporder>3</apporder>
                                <capacities>
                                    <capacity>Capacity 1,7-2,3 litre (Service fill)</capacity>
                                    <capacity>Capacity 1,9-2,5 litre (Initial fill)</capacity>
                                </capacities>
                                <use name="Normal">
                                    <intervals />
                                    <product number="1" apporder="1" productcode="" name="Special Product" id="99999" temperature="" url="" packshot="" />
                                </use>
                            </component>
                            <component name="Hydraulic brake/clutch system, DSC" code="">
                                <number>5</number>
                                <apporder>5</apporder>
                                <capacities />
                                <use name="Normal">
                                    <intervals>
                                        <interval>Change 24 months</interval>
                                    </intervals>
                                    <product number="1" apporder="118" productcode="6404" name="Gulf Brake Fluid DOT 4" id="267631" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1MC8kGfUWhhjt5jF7SX_7MhoI3dlqMs0L/view?usp=sharing" />
                                    <product number="2" apporder="119" productcode="6405" name="Gulf Racing Brakefluid" id="267632" temperature="year-round" url="" packshot="" />
                                    <product number="3" apporder="214" productcode="1" name="Gulf Elec Brake Fluid" id="267727" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1eCLAG04BfP7rELjeb2BaTMP1qpKO_vXQ/view?usp=sharing" />
                                </use>
                            </component>
                            <component name="Power steering, combined with levelling system" code="">
                                <number>6</number>
                                <apporder>6</apporder>
                                <capacities />
                                <use name="Normal">
                                    <intervals />
                                    <product number="1" apporder="1" productcode="" name="Special Product" id="99999" temperature="" url="" packshot="" />
                                </use>
                            </component>
                            <component name="Power steering, separate system (ATF cap)" code="">
                                <number>7</number>
                                <apporder>6</apporder>
                                <capacities />
                                <use name="Normal">
                                    <intervals />
                                    <product number="1" apporder="105" productcode="2460" name="Gulf Multi Vehicle ATF" id="267618" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1heL6zkvgetHjACCZiiP91UpQ4LLyu_hN/view?usp=sharing" />
                                    <product number="2" apporder="106" productcode="2502" name="Gulf ATF DX II" id="267619" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1Ynu21lP5OHvzdMf9a2npPsp8gxXZmhl9/view?usp=sharing" />
                                    <product number="3" apporder="108" productcode="2504" name="Gulf ATF DX IIIH" id="267621" temperature="year-round" url="" packshot="" />
                                </use>
                            </component>
                            <component name="Cooling system" code="">
                                <number>8</number>
                                <apporder>8</apporder>
                                <capacities>
                                    <capacity>Capacity 7,2-7,4 litre</capacity>
                                </capacities>
                                <use name="Normal">
                                    <intervals>
                                        <interval>Change 48 months</interval>
                                    </intervals>
                                    <product number="1" apporder="121" productcode="6901" name="Gulf Antifreeze LL" id="267634" temperature="year-round" url="" packshot="https://drive.google.com/file/d/1c0F-_PrEkg5dvl3dESJI6WLcru9XJuCN/view?usp=sharing" />
                                </use>
                            </component>
                        </brandrange>
                    </advice>
                </ctmessage>
            </TypeID2RecommendationResult>
        </TypeID2RecommendationResponse>
    </soap:Body>
</soap:Envelope>';


	echo 'dffdffhfgfghfghg';
	$array = $array1 = [];
	$xml = preg_replace("/(<\/?)(\w+):([^>]*>)/", '$1$2$3$4$5', $xmlstr);
	$xml = simplexml_load_string($xml);
	$json = json_encode($xml);
	$array1 = json_decode($json, true); // true to have an array, false for an object
	//echo '<pre>';print_r($array1); echo '</pre>';
	
	$array = $array1['soapBody']['TypeID2RecommendationResponse']['TypeID2RecommendationResult']['ctmessage'];
	//echo '<pre>';print_r($array); echo '</pre>';
	$html = ' <section class="brand-information">
				  <div class="container">
					<div class="row">
					  <div class="col-md-11 offset-md-1">
						<div class="page-title">
						  <h1 data-aos="fade-up">Lubricant Advisor</h1>
						  <nav aria-label="breadcrumb" data-aos="fade-up">
							<ol class="breadcrumb">
							  <li class="breadcrumb-item"><a href="#">Home</a></li>
							  <li class="breadcrumb-item active" aria-current="page">
								Lubricant Advisor
							  </li>
							</ol>
						  </nav>
						</div>';
			
			
	$make = $array['vehicle']['make']['@attributes']['name'] ; 
	$model = $array['vehicle']['model']['@attributes']['name'] ;
	$type_val = $array['vehicle']['type']['@attributes']['name'] ;
	$year_range = $array['vehicle']['yearrange']['@attributes']['name'] ;	

    $recommendation_for = $make.' '.$model.' '.$type_val.' '.$year_range;
	
	
	
	$components = $array['advice']['brandrange']['component'];
	echo '<pre>';print_r($components);echo '</pre>'; //die();
	$count =0;$use_condition = $intervals = '';
	//components loop
	foreach($components as $k => $recommendations){ //echo 'k=='.$k;
	  $html .= '<div class="recommed-list">';
	  if($k == 0)
	  {
		   $html .='<div class="subtitle w-border mt-5">
			<h3>Recommendations for your:</h3>
			<p>'.$recommendation_for.'</p>
		  </div>
		  <div class="d-flex">
			 <a id="printid" href="#" class=" print">Print</a>					
		  </div>';
	  }
	  
	  foreach($recommendations as $key => $recommendation){ //echo 'key=='.$key;
		  $recommend_product = [];
		  
		  if($key == '@attributes'){							 
			 $recommend_for = $recommendation['name'].' '.$recommendation['code'];
			 $html .= '<div class="subtitle w-border w-icon mt-3">
						<h3>'.$recommend_for.'</h3>
						<a class=" minimize">
						  <div class="fa fa-minus"></div>
						</a>
					  </div>';
		  }
		 
		  //products loop
		  if($key == 'use'){
			foreach($recommendation as $k => $recommend){
				
				if($k == '@attributes'){
					$use_condition = $recommend['name'];	
				}
				if($k == 'intervals'){
					$intervals = $recommend['interval'];	
				}
				
				echo 'test===='.$k;
				if($k == 'product'){
					
					
					//echo '<pre>'; print_r($recommendation['product']);echo '</pre>';
					
					echo 'count=='.count($recommendation['product']);echo '</pre>';
					
					if(count($recommendation['product'])>1){
						$recommend_product = $recommendation['product'];
					}
					else if(count($recommendation['product']) == 1){
						$recommend_product[] = $recommendation['product'];
					}else {
						$recommend_product =[];
					}

					echo '<pre>'; print_r($recommend_product);echo '</pre>';
					
					
				}  // product close
				
				
				
				
			}
		}  //use loop closed
		if($key == 'capacities'){
			$temp_capacity = []; 
			//echo 'capacities count ==='.count($recommendations['capacities']);echo '</br>';
			if(count($recommendations['capacities']) == 1){
					//echo 'capactiy'.$capacity .= $recommendations['capacities']['capacity']; echo '<br/>';
					if(is_array($recommendations['capacities']['capacity'])){
						$temp_capacity = $recommendations['capacities']['capacity'];
					}
					else {
						$temp_capacity[] = $recommendations['capacities']['capacity'];
					}
			}

			//echo '<pre>';print_r($temp_capacity);echo '</pre>';		
				

	    }  
		  
		  
	  }   // foreach recommendations close
	  
	  
	  
	  $html .= '<div class="recommed-list-content">';
	  $html .= '<div class="row">';
		foreach($recommend_product as $k1 => $recommend1){
			//echo '<pre>';print_r($recommend_product);echo '</pre>';																												
		
			foreach($recommend1 as $k2 => $product){
				
				$input_image = $product['packshot'];
				$image_id = substr($input_image, 32, 33); 
				//echo 'result image id=='.$image_id;echo '</br>';
				$product_image ='https://drive.google.com/uc?export=view&id='.$image_id;
				
				
				
				$html .= '<div class="col-md-4">
							<div class="recommed-list-item">
							  <h4 class="text-truncate">'.$product['name'].'</h4>';
							  
								if (!empty($input_image)) {
								  $html .=	'<div class="text-center">
									<img
									  src="'.$product_image .'"
									  alt=""
									  class="img-fluid"
									/>
								  </div>';
								} else {	
								
								  $html .=	"<div class='text-center'>
									<img
									  src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "'
									  alt=''
									  class='img-fluid'
									/>
								  </div>";
								
								}
							 
				
							  
				if( ($use_condition !='') || $intervals != '' ){		  
					$html .=	'<div class="highlight">
									<ul class="list-unstyled m-0">';
									  if($use_condition !='') $html .= '<li>Use Condition:<span>'.($use_condition).'</span></li>';
									  else $html .='';
									  if($intervals != '') $html .='<li>Intervals:<span>'.$intervals.'</span></li>';
									  else $html .= '';
					$html .=		'</ul>
								  </div>';
				}
							  
				$html .=	'</div>
						  </div>';
				
				
			}
			
		}
		
		
		$html .= '</div>';
		
		$html .= '<div class="tips">
								<strong>Capacity:</strong>
								<ul class="list-inline">';
									foreach($temp_capacity as $temp_key => $temp_capacity_value){
										$t_key = ++$temp_key;
										$html .= '<li class="list-inline-item">'.$t_key.'. ' . $temp_capacity_value . '</p>';
									}
							
				   $html .= 	'</ul>
							</div>';
	  
	  
	  
		$html .= '</div>';	
	  $html .= '</div>';	
	}


    
						
    
			
			
    $html .= '		<div class="text-center mb-4">
					  <a href="/lubricantadvisor/form" class="btn-search"> <span>New Search</span></a>
					</div>	
					</div>
				</div>
			</div>
		</section>';
		
		return array(     
			  '#markup' => $html,
			  '#attached' => array('library' => array('lubricantadvisor/lubricant_js')),
			);
	
  }
  public function typeid2recommendationinfo(){
	$type= isset($_REQUEST['typeid'] )?$_REQUEST['typeid']:'';
	$html  =$intervals = $use_condition ='';$recommendation =$recommend_capacities = [];
	if($type != ''){
		$array = [];
		$config = $this->config('lubricantadvisor.settings');
		$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
		$username = $config->get('username');
		$password = $config->get('password');
		
		//$olyslagerApiWsdl = "https://olyslager-customerapi.lubsadvisor.com/OlyslagerAPI.asmx?wsdl";
		//$username = "Gulf_Oil_GBR_Test";
		//$password = "Wa74TgBy65";
	
		//Set the soap client and enable tracing for debugging
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
		   //"Type"=>$type,
		   "Type"=>"485c77f997875de22917bc900420aa0e",
		);
		//"Type"=>"485c77f997875de22917bc900420aa0e",
		//"b0392122f5084fb3bd04e288179e7141"
		//Debug the xml data returned from the web service
		//Call the soap service and store the response.
		$response = $soapClient->TypeID2Recommendation($parameters);

		//Load the xml with an xml parser
		$xml = simplexml_load_string($response->TypeID2RecommendationResult->any);
		$html ='';
		//Check if parsing succeed
		if ($xml === false) {
		   //echo "\nFailed loading XML: ";
		   $html .= ' <section class="brand-information">
					<div class="container">
					<div class="row">
					<div class="col-md-11 offset-md-1">
					<div class="page-title">
					  <h1 data-aos="fade-up">Lubricant Advisor</h1>
					  <nav aria-label="breadcrumb" data-aos="fade-up">
						<ol class="breadcrumb">
						  <li class="breadcrumb-item"><a href="#">Home</a></li>
						  <li class="breadcrumb-item active" aria-current="page">
							Lubricant Advisor
						  </li>
						</ol>
					  </nav>
					</div>';
				   foreach(libxml_get_errors() as $error) {
					 // echo "\n", $error->message;
					  $html .='<div class="subtitle w-border mt-5"><p>'.$error->message.'</p> </div>';
				   }


$html .=				'</div>
					</div>
				</div>
			</section>';
		   
		} else {

		   //If the first node == status then there is no data and someting wrong with request. See the result code and result description for more information.
		   if ($xml->getName() == "status"){

			  //Print the exception
			  //print "\nWeb service result: ".$xml->resultdescription." (resultcode: ".$xml->resultcode.")\n";
			  
			  $html .= ' <section class="brand-information">
				  <div class="container">
					<div class="row">
					  <div class="col-md-11 offset-md-1">
						<div class="page-title">
						  <h1 data-aos="fade-up">Lubricant Advisor</h1>
						  <nav aria-label="breadcrumb" data-aos="fade-up">
							<ol class="breadcrumb">
							  <li class="breadcrumb-item"><a href="#">Home</a></li>
							  <li class="breadcrumb-item active" aria-current="page">
								Lubricant Advisor
							  </li>
							</ol>
						  </nav>
						</div>';
			$html .='<div class="subtitle w-border mt-5"><p>'.$xml->resultdescription.'(  resultcode: ' .$xml->resultcode.')</p> </div>';
			$html .=	'</div>
					</div>
				</div>
			</section>';
						
				
			  
		   }
		   else {

			   //print "\nWeb service result: ".$xml->status->resultdescription." (resultcode: ".$xml->status->resultcode.")\n";

			  //Print the number of records returned by the web service
			  
			  //print "\nNumber of records: ".$xml->ctdata->resultset['numrecords']."\n\n";
			  
			  $array = json_decode(json_encode((array) $xml), 1);
			  
			 
			  //echo '<pre>';print_r($array);echo '</pre>';die();
			  
			  // recommendation page new html layout rendered code
			  $html .= ' <section class="brand-information">
				  <div class="container">
					<div class="row">
					  <div class="col-md-11 offset-md-1">
						<div class="page-title">
						  <h1 data-aos="fade-up">Lubricant Advisor</h1>
						  <nav aria-label="breadcrumb" data-aos="fade-up">
							<ol class="breadcrumb">
							  <li class="breadcrumb-item"><a href="#">Home</a></li>
							  <li class="breadcrumb-item active" aria-current="page">
								Lubricant Advisor
							  </li>
							</ol>
						  </nav>
						</div>';
			
			
	$make = $array['vehicle']['make']['@attributes']['name'] ; 
	$model = $array['vehicle']['model']['@attributes']['name'] ;
	$type_val = $array['vehicle']['type']['@attributes']['name'] ;
	$year_range = $array['vehicle']['yearrange']['@attributes']['name'] ;	

    $recommendation_for = $make.' '.$model.' '.$type_val.' '.$year_range;
	
	
	
	$components = $array['advice']['brandrange']['component'];
	echo '<pre>';print_r($components);echo '</pre>'; //die();
	$count =0;
	//components loop
	foreach($components as $k => $recommendations){ //echo 'k=='.$k;
	  $html .= '<div class="recommed-list">';
	  if($k == 0)
	  {
		   $html .='<div class="subtitle w-border mt-5">
			<h3>Recommendations for your:</h3>
			<p>'.$recommendation_for.'</p>
		  </div>
		  <div class="d-flex">
			 <a id="printid" href="#" class=" print">Print</a>					
		  </div>';
	  }
	  $use_condition = $intervals = '';
	  foreach($recommendations as $key => $recommendation){ //echo 'key=='.$key;
		  $recommend_product = [];
		  
		  if($key == '@attributes'){							 
			 $recommend_for = $recommendation['name'].' '.$recommendation['code'];
			 $html .= '<div class="subtitle w-border w-icon mt-3">
						<h3>'.$recommend_for.'</h3>
						<a class="minimize">
						  <div class="fa fa-minus"></div>
						</a>
					  </div>';
		  }
		 
		  //products loop
		  if($key == 'use'){
			foreach($recommendation as $k => $recommend){
				
				if($k == '@attributes'){
					$use_condition = $recommend['name'];	
				}
				if($k == 'intervals'){
					$intervals = $recommend['interval'];	
				}
				
				echo 'test===='.$k;
				if($k == 'product'){
					
					
					//echo '<pre>'; print_r($recommendation['product']);echo '</pre>';
					
					echo 'count=='.count($recommendation['product']);echo '</pre>';
					
					if(count($recommendation['product'])>1){
						$recommend_product = $recommendation['product'];
					}
					else if(count($recommendation['product']) == 1){
						$recommend_product[] = $recommendation['product'];
					}else {
						$recommend_product =[];
					}

					echo '<pre>'; print_r($recommend_product);echo '</pre>';
					
					
				}  // product close
				
				
				
				
			}
		}  //use loop closed
		if($key == 'capacities'){
			$temp_capacity = []; 
			//echo 'capacities count ==='.count($recommendations['capacities']);echo '</br>';
			if(count($recommendations['capacities']) == 1){
					//echo 'capactiy'.$capacity .= $recommendations['capacities']['capacity']; echo '<br/>';
					if(is_array($recommendations['capacities']['capacity'])){
						$temp_capacity = $recommendations['capacities']['capacity'];
					}
					else {
						$temp_capacity[] = $recommendations['capacities']['capacity'];
					}
			}

			//echo '<pre>';print_r($temp_capacity);echo '</pre>';		
				

	    }  
		  
		  
	  }   // foreach recommendations close
	  
	  
	  
	  $html .= '<div class="recommed-list-content">';
	  $html .= '<div class="row">';
		foreach($recommend_product as $k1 => $recommend1){
			//echo '<pre>';print_r($recommend_product);echo '</pre>';																												
		
			foreach($recommend1 as $k2 => $product){
				
				$input_image = $product['packshot'];
				$image_id = substr($input_image, 32, 33); 
				//echo 'result image id=='.$image_id;echo '</br>';
				$product_image ='https://drive.google.com/uc?export=view&id='.$image_id;
				
				
				
				$html .= '<div class="col-md-4">
							<div class="recommed-list-item">
							  <h4 class="text-truncate">'.$product['name'].'</h4>';
							  
								if (!empty($input_image)) {
								  $html .=	'<div class="text-center">
									<img
									  src="'.$product_image .'"
									  alt=""
									  class="img-fluid"
									/>
								  </div>';
								} else {	
								
								  $html .=	"<div class='text-center'>
									<img
									  src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "'
									  alt=''
									  class='img-fluid'
									/>
								  </div>";
								
								}
							 
				
				if( ($use_condition !='') || $intervals != '' ){		  
					$html .=	'<div class="highlight">
									<ul class="list-unstyled m-0">';
									  if($use_condition !='') $html .= '<li>Use Condition:<span>'.($use_condition).'</span></li>';
									  else $html .='';
									  if($intervals != '') $html .='<li>Interval:<span>'.$intervals.'</span></li>';
									  else $html .= '';
					$html .=		'</ul>
								  </div>';
				}			  
				$html .=	'</div>
						  </div>';
				
				
			}
			
		}
		
		
		$html .= '</div>';
		
		$html .= '<div class="tips">
								<strong>Capacity:</strong>
								<ul class="list-inline">';
									foreach($temp_capacity as $temp_key => $temp_capacity_value){
										$t_key = ++$temp_key;
										$html .= '<li class="list-inline-item">'.$t_key.'. ' . $temp_capacity_value . '</p>';
									}
							
				   $html .= 	'</ul>
							</div>';
	  
	  
	  
		$html .= '</div>';	
	  $html .= '</div>';	
	}


    
						
    
			
			
    $html .= '		<div class="text-center mb-4">
					  <a href="/lubricantadvisor/form" class="btn-search"> <span>New Search</span></a>
					</div>	
					</div>
				</div>
			</div>
		</section>';
		
		
			  
			  
			  
			  //  recommendation page new html layout rendered code
			  
			  
			  
			  
		   } // else success code 200
		} // xml success else close
		
		return array(     
			  '#markup' => $html,
			  '#attached' => array('library' => array('lubricantadvisor/lubricant_js')),
			);
		
	}  // type close			
	  
  }//recommnedation close
  
  
  public function typeid2recommendationinfoOld(){
		$type= isset($_REQUEST['typeid'] )?$_REQUEST['typeid']:'';
		$html  =$intervals = $use_condition ='';$recommendation =$recommend_capacities = [];
		if($type != ''){
			$array = [];
			$config = $this->config('lubricantadvisor.settings');
			$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
			$username = $config->get('username');
			$password = $config->get('password');
			
			//$olyslagerApiWsdl = "https://olyslager-customerapi.lubsadvisor.com/OlyslagerAPI.asmx?wsdl";
			//$username = "Gulf_Oil_GBR_Test";
			//$password = "Wa74TgBy65";
		
			//Set the soap client and enable tracing for debugging
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
			   "Type"=>$type,
			);
			//"Type"=>"485c77f997875de22917bc900420aa0e",
			//"b0392122f5084fb3bd04e288179e7141"
			//Debug the xml data returned from the web service
			//Call the soap service and store the response.
			$response = $soapClient->TypeID2Recommendation($parameters);
			



			//Load the xml with an xml parser
			$xml = simplexml_load_string($response->TypeID2RecommendationResult->any);
			
			
			


			//Check if parsing succeed
			if ($xml === false) {
			   echo "\nFailed loading XML: ";
			   foreach(libxml_get_errors() as $error) {
				  echo "\n", $error->message;
			   }
			} else {

			   //If the first node == status then there is no data and someting wrong with request. See the result code and result description for more information.
			   if ($xml->getName() == "status"){

				  //Print the exception
				  print "\nWeb service result: ".$xml->resultdescription." (resultcode: ".$xml->resultcode.")\n";
			   }
			   else {

				   //print "\nWeb service result: ".$xml->status->resultdescription." (resultcode: ".$xml->status->resultcode.")\n";

				  //Print the number of records returned by the web service
				  print "\nNumber of records: ".$xml->ctdata->resultset['numrecords']."\n\n";
				  
				  $array = json_decode(json_encode((array) $xml), 1);
				  
				 
				  //echo '<pre>';print_r($array);echo '</pre>';die();
				  
				  $make = $array['vehicle']['make']['@attributes']['name'] ; 
				  $model = $array['vehicle']['model']['@attributes']['name'] ;
				  $type_val = $array['vehicle']['type']['@attributes']['name'] ;
				  $year_range = $array['vehicle']['yearrange']['@attributes']['name'] ;
				  
				  
				  $html .= "<section class='brand-information'><div class='container'><div class='row'><div class='col-md-11 offset-md-1'><div class='page-title'>
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
				<div class='d-flex justify-content-between align-items-center'>
					<div class='recomd-sec w-100'>
						<p> Recommendations for your : </p><h4>" . $make . " " . $model ." ". $type_val. " " .$year_range."</h4>
					</div>
					<div class='d-flex'>
						<a id='printid' href='#' class=' print'>Print</a>					
					</div>
				</div>
				";
				 $html .= "<div class='hr-line'><hr></div>";   
    
				 $html .='<div class="col-md-12 pad-0 lubricant_view active">';				  
				  
				  
				  $components = $array['advice']['brandrange']['component'];
				  //echo '<pre>';print_r($components);echo '</pre>';
				  
				  foreach($components as $k => $recommendations){
					  foreach($recommendations as $key => $recommendation){
					    $capacity ='';
						if($key == '@attributes'){							 
							 $recommendation_for = $recommendation['name'].' '.$recommendation['code'];	
							 $html .= "<div class='row'>
											<div class='col-md-6 text-left'>
												<h3 class='equipment_title py-1'>" . $recommendation_for . "</h3>
											</div>
											<div class='col-md-6 text-end'>
												<img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/minus.png' . "' data-eqcontent='eqcontant".$k."' >
											</div>
										</div>
										<hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$k." active'>";
							  
						}
						$html .= "<div class='row'>";
						
						
						if($key == 'use'){
							foreach($recommendation as $k => $recommend){
								
								
								if($k == 'product'){
									
									$recommend_product = [];
									//echo '<pre>'; print_r($recommendation['product']);echo '</pre>';
									
									//echo 'count=='.count($recommendation['product']);echo '</pre>';
									
									if(count($recommendation['product'])>1){
										$recommend_product = $recommendation['product'];
									}
									else if(count($recommendation['product']) == 1){
										$recommend_product[] = $recommendation['product'];
									}else {
										$recommend_product =[];
									}

									//echo '<pre>'; print_r($recommend_product);echo '</pre>';
									
									foreach($recommend_product as $k1 => $recommend1){
										//echo '<pre>';print_r($recommend_product);echo '</pre>';
																																				
									
										foreach($recommend1 as $k2 => $product){
											
											$html .= '<div class=\'lubricant-result col-sm-4\'>
														<div class="lubricant-inner-section">';
														$html .= '<div class="product_img">';
															$html .= "<h3 class='heading'>" . $product['name'] . "</h3><br/>";
														//$imput_image = 'https://drive.google.com/file/d/1_DZNs87eflgX9iboS3kjqP3w-VBvKR-T/view';
														$input_image = $product['packshot'];
														$image_id = substr($input_image, 32, 33); 
														//echo 'result image id=='.$image_id;echo '</br>';
														$product_image ='https://drive.google.com/uc?export=view&id='.$image_id;
														if (!empty($product['packshot'])) {
														  $html .= "<img src=\"" . $product_image . "\" ></img>";
														} else {
														  $html .= "<img src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
														}
														$html .= '</div>';
														$html .= "<div class='additional_info'>";
															$html .= "<span>".$product['temperature']."</span>";
														$html .= "</div>";
									
											//echo 'product_name'.$product_name .= ','.$product['name'].',';echo '<br/>';
											//echo 'product_code'.$product_code .= $product['productcode'].',';echo '<br/>';
											//echo 'product_id'.$product_id .= $product['id'].',';echo '<br/>';
											//echo 'product_url'.$product_url .=$product['url'].',';echo '<br/>';
											//echo 'product_image'.$product_image .=$product['packshot'].',';echo '<br/>';
											//echo 'product_temp'.$product_temp .= $product['temperature'].',';echo '<br/>';
											
											$html .='</div></div>';
										}
										
									}
								}
								
								if($k == '@attributes'){
									$use_condition = $recommend['name'];
									if($use_condition){
										
									}
									$html .= "<div class=\'lubricant-result col-sm-12\'><div class='additional_info'>";
									$html .= "<p><span>Use condition: ".$use_condition."</span></p>";
									$html .="</div></div>";
									
								}
								if($k == 'intervals'){
									$intervals = $recommend['interval'];
									$html .= "<div class=\'lubricant-result col-sm-4\'><div class='additional_info'>";
									$html .= "<p>Interval: ".$intervals."</p>";
									$html .="</div></div>";
								}
								
								
							}
						}
						
						$html .= "</div>";
						
						$html .= "<div class='additional_info'>";
						if($key == 'capacities'){
							$temp_capacity = []; 
							//echo 'capacities count ==='.count($recommendations['capacities']);echo '</br>';
							if(count($recommendations['capacities']) == 1){
									//echo 'capactiy'.$capacity .= $recommendations['capacities']['capacity']; echo '<br/>';
									if(is_array($recommendations['capacities']['capacity'])){
										$temp_capacity = $recommendations['capacities']['capacity'];
									}
									else {
										$temp_capacity[] = $recommendations['capacities']['capacity'];
									}
							}
							
							//echo '<pre>';print_r($temp_capacity);echo '</pre>';
							
							foreach($temp_capacity as $temp_key => $temp_capacity_value){
								$html .= "<p>Capacity: <b>" . $temp_capacity_value . "</b></p>";
							}
							
						}
						$html .= "</div>";
						
						
						
					}
				  }
				  
				  
				  //die();
				  
				   $html .= "</div>";
				   $html .= '<hr><div class="text-center mb-4"><a href="/lubricantadvisor/form" class="btn-search"> <span>New Search</span></a></div>'; 
				   $html .= "</div></div></div></section>";
			   }
			}
			
			
			
			return array(     
			  '#markup' => $html,
			  '#attached' => array('library' => array('lubricantadvisor/lubricant_js')),
			);
			
		}
   }
 
  

  public function equipmentinfo($equipment_name = '') {
    $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/equipment/" . $equipment_name . "?token=ldWMYvB1ryWu";
    $client = \Drupal::httpClient();
    $request = $client->get($url, [
      'headers' => [
        'Content-Type' => 'application/xml',
      ],
      'timeout' => 10000,
    ]);
    $json_string = (string) $request->getBody();
    $fileContents = str_replace(array("\n", "\r", "\t"), '', $json_string);

    $fileContents = trim(str_replace('"', "'", $fileContents));

    $simpleXml = simplexml_load_string($fileContents);
    $attr_item_Data = json_decode(json_encode((array) $simpleXml), TRUE);
    $html = '';
	
	/*$html .= "<div class='sub-hero'>
		  <picture>
			<source
			  media='(min-width:576px)'
			  srcset='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/hero-lubricant-m.jpg' . "'
			  class='img-fluid'
			/>		 
			<img  src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/hero-lubricant.jpg' . "'  alt='' class='img-fluid' />
		  </picture>
		</div>";
    */
	
	
    //$html .= "<div class='row'><div class='col-md-6'><b class='heading1'> Recommendations for your : </b><br/><b class='heading'>" . $attr_item_Data['equipment']['manufacturer'] . " " . $attr_item_Data['equipment']['model'] . "</b><br/></div><div class='col-md-4 text-right'><br/><div class='toggle_content'></div></div><div class='col-md-1 text-right'><br/><a id='printid' href='#' class='newsearch1'>Print</a></div><div class='col-md-1 text-right'><br/><a id='mail' href='/lubricant/recommandation/".$equipment_name."' class='newsearch1 use-ajax button'>Email</a></div></div>";
    $html .= "<section class='brand-information'><div class='container'><div class='row'><div class='col-md-11 offset-md-1'><div class='page-title'>
              <h1 data-aos='fade-up'>Lubricant Finder</h1>
              <nav aria-label='breadcrumb' data-aos='fade-up'>
                <ol class='breadcrumb'>
                  <li class='breadcrumb-item'><a href='#'>Home</a></li>
                  <li class='breadcrumb-item active' aria-current='page'>
                    Lubricant Finder
                  </li>
                </ol>
              </nav>
            </div>
			<div class='d-flex justify-content-between align-items-center'>
				<div class='recomd-sec w-100'>
					<p> Recommendations for your : </p><h4>" . $attr_item_Data['equipment']['manufacturer'] . " " . $attr_item_Data['equipment']['model'] . "</h4>
				</div>
				<div class='d-flex'>
					<a id='printid' href='#' class=' print'>Print</a>					
				</div>
			</div>
			";
			
	/* <a id='mail' href='/lubricantadvisor/email_popup/".$equipment_name."' class=' email use-ajax button'>Email</a>*/
    $html .= "<div class='hr-line'><hr></div>";
    
    $note_start_index = 0;
    $html .='<div class="col-md-12 pad-0 lubricant_view active">';

    foreach ($attr_item_Data['equipment']['application'] as $key => $value) {
      //echo '<pre>';var_dump($key);echo '</pre>';
      //echo '<pre>';var_dump($value);echo '</pre>';

      //echo 'display name ======='.$value['display_name'];echo '</br>';
      
          
      if (isset($value['product']['name'])) {
        $html .= "<div class='row'><div class=' col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/minus.png' . "' data-eqcontent='eqcontant".$key."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$key." active'>";
        $html .= "<div class='row'>"; 
        $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
        $html .= '<div class="product_img">';
        $html .= "<h3 class='heading'>" . $value['product']['name'] . "</h3><br/>";
        if (isset($value['product']['resource'])) {
            //echo 'image====='.$value['product']['resource']['@attributes']['href'].'<br/>';
            if (isset($value['product']['resource']['@attributes']['href'])) {
              $html .= "<img src=\"" . $value['product']['resource']['@attributes']['href'] . "\" ></img>";
            } else {
              $html .= "<img src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
            }
         }
        $html .= '</div>';
        if (isset($value['product']['@attributes']['tier_name']) || isset($value['display_capacity'])) {
          $html .= "<div class='additional_info'>";
        }
        if (isset($value['product']['@attributes']['tier_name'])) {
          $html .= "Recommendations: <b>" . $value['product']['@attributes']['tier_name'] . "</b><br/>";
        }
        if (isset($value['display_capacity'])) {
          $html .= "Capacity: <b>" . $value['display_capacity'] . "Liters</b>";
        } else {
          $html .= "<br/>";
        }
        if (isset($value['product']['@attributes']['tier_name']) || isset($value['display_capacity'])) {
          $html .= "</div>";
        }
        $html .= '</div></div>';
      } elseif (isset($value['product'])) {
        $html .= "<div class='row'><div class='col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/minus.png' . "' data-eqcontent='eqcontant".$key."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$key." active'>";
        $html .= "<div class='row'>"; 
        
        foreach ($value['product'] as $product_key => $product_value) {
          //echo '<pre>';var_dump($product_key);echo '</pre>';
          //echo '<pre>';var_dump($product_value);echo '</pre>';
          if (isset($product_value['name'])) {
            $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
            $html .= "<h3 class='heading'>" . $product_value['name'] . "</h3>";
            $html .= '<div class="product_img">';
          }
          if (isset($product_value['resource'])) {
            //echo  $product_key.'product image ==== '.$product_value['resource']['@attributes']['href'].'</br>';
            if (isset($product_value['resource']['@attributes']['href'])) {
              $html .= "<img src=\"" . $product_value['resource']['@attributes']['href'] . "\" ></img>";
            } else {
              $html .= "<img src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
            }
          }
          if (isset($product_value['name'])) {
            $html .= "</div>";
            if (isset($product_value['@attributes']['tier_name']) || isset($value['display_capacity'])) {
              $html .= "<div class='additional_info'>";
            }
            if (isset($product_value['@attributes']['tier_name'])) {
              $html .= "Recommendations: <b>" . $product_value['@attributes']['tier_name'] . "</b><br/>";
            }
            if (isset($value['display_capacity'])) {
              $html .= "Capacity: <b>" . $value['display_capacity'] . "Liters</b>";
            } else {
              $html .= "<br/>";
            }
            if (isset($product_value['@attributes']['tier_name']) || isset($value['display_capacity'])) {
              $html .= "</div>";
            }
            $html .= "</div></div>";
          }
        }
      } else {
       

        if (isset($value['display_capacity'])) {
          $html .= "<div class='row'><div class='col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/minus.png' . "' data-eqcontent='eqcontant".$key."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$key." active'>";
          $html .= "<div class='    row'>";          
          $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
          /*$html .= '<div class="product_img">';
          $html .= "<img src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
          $html .= "</div>";*/

          $html .= "<div class='additional_info'>";
          $html .= "Capacity: <b>" . $value['display_capacity'] . "Liters</b>";
        } else {
          $html .= "<br/>";
        }

        if (isset($value['display_capacity'])) {
          $html .= "</div></div></div>";
        }
        
       
       

        //$html .= "</div>";

        

      }
      $html .= "</div>"; 
      if (isset($value['note_ref']['@attributes'])) {
        $html .= '<div class="col-md-12 pad-0 note-text"><b class="tips_title">Tips : </b>';
        if (is_array($attr_item_Data['equipment']['app_note'])) {
          $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
          $note_start_index++;
        } else {
          $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'] . '&nbsp;&nbsp;';
          
        }
        $html .= "</div>";
      } elseif (isset($value['note_ref'])) {
        $html .= '<div class="row"><b class="tips_title">Tips : </b>';
        foreach ($value['note_ref'] as $note_key => $note_value) {
          if ($note_key > 0) {
            $html .= '| &nbsp; ';
          }
          $html .= $note_value['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
          $note_start_index++;
        }
        $html .= "</div>";
      }  
      $html .= "</div>";    
     
    }
    $html .= "</div></div></div></section>";
 
///Noramal View

$html .='<div class="row lubricant_view d-none">';
$note_start_index = 0;
$table_eqcontent=100;
    foreach ($attr_item_Data['equipment']['application'] as $key => $value) {
      
      $html .= "<div class='row'><div class='col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/minus.png' . "' data-eqcontent='eqcontant".$table_eqcontent."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$table_eqcontent." active'>";     
      if (isset($value['product']['name']))   {
        $html .= '<table width="100%" border="0"><thead ><tr border="20"><th width="35%" >Product</th><th width="35%">Description</th><th width="15%">Recommendation</th><th width="15%">Capacity</th></tr></thead><tbody>';
        $html .= "<tr class='tbl_bdr'><td>";
        if (isset($value['product']['name'])) {
          if (isset($value['product']['resource'][0]['@attributes']['href'])) {           
            $html .= "<table class='innertable'><tr><td width='30%'><img width='100px' height='120px' src=\"". $value['product']['resource'][0]['@attributes']['href'] . "\" ></img></td>";
            $html .= "<td width='80%' class='equ_nametable'>".$value['product']['name'] ."</td></tr></table>";
          }
          else
          {
            $html .= "<table class='innertable'><tr>";
            $html .= "<td width='30%'>";
            $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
            $html.="</td>";
            $html.="<td width='80%' class='equ_nametable'>".$value['product']['name'] ."</td>";
            $html.="</tr></table>";
          }        
        } elseif (isset($value['product'])) {
          foreach ($value['product'] as $product_key => $product_value) { 
            if (isset($product_value['resource'][0]['@attributes']['href'])) {
              $html .= "<table class='innertable'><tr>";  
              $html .= "<td width='30%'>";    
              $html .= "<img width='100px' height='120px' src=\"". $product_value['resource'][0]['@attributes']['href'] . "\" ></img>";
              $html.="</td>";
              $html.="<td width='80%' class='equ_nametable'>".$product_value['name'] ."</td>";
              $html.="</tr></table>";
            }
            else
            {
              $html .= "<table class='innertable'><tr>";
              $html .= "<td width='30%'>";
              $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
              $html.="</td>";
              $html.="<td width='80%' class='equ_nametable'>".$product_value['product']['name'] ."</td></tr></table>";
            }
            
          }
        }
        $html .= "</td>";
        $html .= "<td></td>";
        if (isset($value['product']['@attributes']['tier_name'])) {
          $html .= "<td class='text-center'>";
          $html .= $value['product']['@attributes']['tier_name'] . "</td>";        
          
        }
        else
        {
          $html .= "<td></td>";
        }

        if (isset($value['display_capacity'])) {
          $html .= "<td class='text-center'>";
          $html .= $value['display_capacity'] . " </td>";
        } else {
          $html .= "<td></td>";
        }
        $html .= "</tr></tbody></table>";
        $html .= '<br/>';
        if (isset($value['note_ref']['@attributes'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          if (is_array($attr_item_Data['equipment']['app_note'])) {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          } else {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'] . '&nbsp;&nbsp;';
          }
        } elseif (isset($value['note_ref'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          foreach ($value['note_ref'] as $note_key => $note_value) {
            if ($note_key > 0) {
              $html .= '| &nbsp; ';
            }
            $html .= $note_value['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          }
        } 
      } elseif (isset($value['product'])) {
        $html .= '<table width="100%" border="0"><thead><tr border="20"><th width="35%" >Product</th><th width="35%">Description</th><th width="15%">Recommendation</th><th width="15%">Capacity</th></tr></thead><tbody>';
        foreach ($value['product'] as $product_key => $product_value) {
           $html .= "<tr class='tbl_bdr'>";            
           //Product with Name starts 
            if (isset($product_value['name'])) {   
              $html .= "<td>";
              if (isset($product_value['resource'][0]['@attributes']['href'])) {                  
                  $html .= "<table class='innertable'><tr><td width='30%'><img width='100px' height='120px' src=\"". $product_value['resource'][0]['@attributes']['href'] . "\" ></img></td>";
                  $html .= "<td width='80%' class='equ_nametable'>".$product_value['name'] ."</td></tr></table>";
                }
                else
                {
                  $html .= "<table class='innertable'><tr>";
                  $html .= "<td width='30%'>";
                  $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
                  $html.="/td";
                  $html.="<td width='80%' class='equ_nametable'>".$product_value['name'] ."</td>";
                  $html.="</tr></table>"; 
                }                        
              $html .= "</td>";              
            }
            else
            {
              $html .= "<td></td>";

            }
            //description starts
            $html .= "<td></td>";
            if (isset($product_value['@attributes']['tier_name'])) {
              $html .= "<td class='text-center'>"; 
              $html .=  $product_value['@attributes']['tier_name'] . "</td>";
            }   
            else
            {
              $html .= "<td></td>";
            }
            if (isset($value['display_capacity'])) {
              $html .= "<td class='text-center'>" . $value['display_capacity'] . "</td>";
            } else {
              $html .= "<td></td>";
            }         
           $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        $html .= '<br/>';
        if (isset($value['note_ref']['@attributes'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          if (is_array($attr_item_Data['equipment']['app_note'])) {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          } else {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'] . '&nbsp;&nbsp;';
          }
        } elseif (isset($value['note_ref'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          foreach ($value['note_ref'] as $note_key => $note_value) {
            if ($note_key > 0) {
              $html .= '| &nbsp; ';
            }
            $html .= $note_value['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          }
        }        
      } else {
         $html .= '<table width="100%" border="0"><thead ><tr border="20"><th width="35%" >Product</th><th width="35%">Description</th><th width="15%">Recommendation</th><th width="15%">Capacity</th></tr></thead><tbody>';
        $html .= "<tr class='tbl_bdr'><td>";
        //$html .= "<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantadvisor') . '/images/no_image.png' . "' ></img>";
        $html .= "</td>";
        $html .= "<td></td>";
        if (isset($product_value['@attributes']['tier_name'])) {
          $html .= "<td class='text-center'>"; 
          $html .=  $product_value['@attributes']['tier_name'] . "</td>";
        }   
        else
        {
          $html .= "<td></td>";
        }
        if (isset($value['display_capacity'])) {         
          $html .= "<td class='text-center'>" . $value['display_capacity'] . "</td>";
        } else {
          $html .= "<td></td>";
        }
         $html .= "</tr></tbody></table>";  
         $html .= '<br/>';
        if (isset($value['note_ref']['@attributes'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          if (is_array($attr_item_Data['equipment']['app_note'])) {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          } else {
            $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'] . '&nbsp;&nbsp;';
          }
        } elseif (isset($value['note_ref'])) {
          $html .= '<br/><b class="tips_title">Tips : </b>';
          foreach ($value['note_ref'] as $note_key => $note_value) {
            if ($note_key > 0) {
              $html .= '| &nbsp; ';
            }
            $html .= $note_value['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . '&nbsp;&nbsp;';
            $note_start_index++;
          }
        }       

      }
       
     $table_eqcontent++;
     $html .= "</div>";
    }
    $html .= "</div>";

///

    $html .= '<hr><div class="text-center mb-4"><a href="/lubricantadvisor/form" class="btn-search"> <span>New Search</span></a></div>';
    $html .= '</div></div></div>';

    
    // echo "<pre>";
     //print_r($attr_item_Data);
    // echo "</pre>";
    // exit;

    
      
    return array(     
      '#markup' => $html,
      '#attached' => array('library' => array('lubricantadvisor/lubricant_js')),
    );
    
    
  }
  
  

  // Search finder
  public function lubricantadvisorsearch($field_data = '', $field_data1 = '', $field_data2 = '', $field_data3 = '', $field_data4 = '', $field_data5 = '', $field_data6 = '', $field_data7 = '') {
    if ($field_data != '' && $field_data != 'json') {
      $parameter = "/" . $field_data;
    }
    if ($field_data1 != '' && $field_data1 != 'json') {
      $parameter .= "/" . $field_data1;
    }
    if ($field_data2 != '' && $field_data2 != 'json') {
      $parameter .= "/" . $field_data2;
    }
    if ($field_data3 != '' && $field_data3 != 'json') {
      $parameter .= "/" . $field_data3;
    }
    if ($field_data4 != '' && $field_data4 != 'json') {
      $parameter .= "/" . $field_data4;
    }
    if ($field_data5 != '' && $field_data5 != 'json') {
      $parameter .= "/" . $field_data5;
    }
    if ($field_data6 != '' && $field_data6 != 'json') {
      $parameter .= "/" . $field_data6;
    }
    if ($field_data7 != '' && $field_data7 != 'json') {
      $parameter .= "/" . $field_data7;
    }
    $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/search" . $parameter . "?token=ldWMYvB1ryWu";
    $client = \Drupal::httpClient();
    $request = $client->get($url, [
      'headers' => [
        'Content-Type' => 'application/xml',
      ],
      'timeout' => 10000,
    ]);
    $json_string = (string) $request->getBody();
    $fileContents = str_replace(array("\n", "\r", "\t"), '', $json_string);

    $fileContents = trim(str_replace('"', "'", $fileContents));

    $simpleXml = simplexml_load_string($fileContents);
    $attr_item_Data = json_decode(json_encode((array) $simpleXml), TRUE);
    foreach ($simpleXml->children() as $child) {
      foreach ($child->children() as $key => $value) {
        foreach ($value->attributes() as $key1 => $value1) {
          $attr_Data = json_decode(json_encode((array) $value), TRUE);
          if ($key1 == 'href') {
            $attr_values[$attr_Data[0]] = $attr_Data['@attributes'];
          }
        }
        foreach ($value->children() as $keys => $values) {
          foreach ($values->attributes() as $key2 => $value2) {
            $attr_Data = json_decode(json_encode((array) $values), TRUE);
            if ($key2 == 'href') {
              $attr_values[$attr_Data[0]] = $attr_Data['@attributes'];
            }
          }
        }
      }
    }
    foreach ($attr_item_Data as $key => $value) {
      $json_attr_values['href'] = $value['@attributes']['href'];
      if (isset($value['parent'])) {
        if (isset($value['parent']['@attributes'])) {
          $record_no = 0;
          if (count($value['parent']['item']) > 1) {
            foreach ($value['parent']['item'] as $item_key => $item_value) {
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['name'] = $item_value;
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = false;
              if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
                $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = true;
              }
              $record_no++;
            }
          } else {
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['name'] = $value['parent']['item'];
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
            $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = false;
            if (strrpos($value['@attributes']['href'], $attr_values[$value['parent']['item']]['href']) !== false) {
              $json_attr_values['browse'][$value['parent']['@attributes']['type']][$record_no]['selected'] = true;
            }
          }
        } else {
          foreach ($value['parent'] as $parent_key => $parent_value) {
            $record_no = 0;
            if (count($parent_value['item']) > 1) {
              foreach ($parent_value['item'] as $item_key => $item_value) {
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['name'] = $item_value;
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = false;
                if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
                  $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = true;
                }
                $record_no++;
              }
            } else {
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['name'] = $parent_value['item'];
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
              $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = false;
              if (strrpos($value['@attributes']['href'], $attr_values[$parent_value['item']]['href']) !== false) {
                $json_attr_values['browse'][$parent_value['@attributes']['type']][$record_no]['selected'] = true;
              }
            }
          }
        }
      }
      if (isset($value['item'])) {
        if (count($value['item']) == 1) {
          $record_no = 0;
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['name'] = $value['item'];
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['href'] = $attr_values[$value['item']]['href'];
          $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = false;
          if (strrpos($value['@attributes']['href'], $attr_values[$value['item']]['href']) !== false) {
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = true;
          }
        } else {
          $record_no = 0;
          foreach ($value['item'] as $item_key => $item_value) {
            //print_r($item_value);
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['name'] = $item_value;
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['href'] = $attr_values[$item_value]['href'];
            $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = false;
            if (strrpos($value['@attributes']['href'], $attr_values[$item_value]['href']) !== false) {
              $json_attr_values['browse'][$value['@attributes']['type']][$record_no]['selected'] = true;
            }
            $record_no++;
          }
        }
      }
      if (isset($value['equipment'])) {
        if (isset($value['equipment']['@attributes'])) {
          $json_attr_values['equipment'][0]['@btid'] = $value['equipment']['@attributes']['btid'];
          $json_attr_values['equipment'][0]['@cxid'] = $value['equipment']['@attributes']['cxid'];
          $json_attr_values['equipment'][0]['@guid'] = $value['equipment']['@attributes']['guid'];
          $json_attr_values['equipment'][0]['@href'] = $value['equipment']['@attributes']['href'];
          $json_attr_values['equipment'][0]['@id'] = $value['equipment']['@attributes']['id'];
          $json_attr_values['equipment'][0]['@language'] = $value['equipment']['@attributes']['language'];
          $json_attr_values['equipment'][0]['family']['@original'] = $value['equipment']['family'];
          $json_attr_values['equipment'][0]['family']['#text'] = $value['equipment']['family'];
          $json_attr_values['equipment'][0]['familygroup']['@original'] = $value['equipment']['familygroup'];
          $json_attr_values['equipment'][0]['familygroup']['#text'] = $value['equipment']['familygroup'];
          $json_attr_values['equipment'][0]['manufacturer'] = $value['equipment']['manufacturer'];
          $json_attr_values['equipment'][0]['manufacturer_original'] = $value['equipment']['manufacturer_original'];
          $json_attr_values['equipment'][0]['model'] = $value['equipment']['model'];
          $json_attr_values['equipment'][0]['model_original'] = $value['equipment']['model_original'];
          $json_attr_values['equipment'][0]['alt_fueltype'] = $value['equipment']['alt_fueltype'];
          $json_attr_values['equipment'][0]['alt_fueltype_original'] = $value['equipment']['alt_fueltype_original'];
          $json_attr_values['equipment'][0]['series']['@original'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['series']['#text'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['displacement'] = $value['equipment']['series'];
          $json_attr_values['equipment'][0]['yearfrom'] = $value['equipment']['yearfrom'];
          $json_attr_values['equipment'][0]['yearto'] = $value['equipment']['yearto'];
          $json_attr_values['equipment'][0]['display_year'] = $value['equipment']['display_year'];
          $json_attr_values['equipment'][0]['display_name_short'] = $value['equipment']['display_name_short'];
          $json_attr_values['equipment'][0]['fueltype']['@original'] = $value['equipment']['fueltype'];
          $json_attr_values['equipment'][0]['fueltype']['#text'] = $value['equipment']['fueltype'];
          $json_attr_values['equipment'][0]['display_name_long'] = $value['equipment']['display_name_long'];

        } else {
          foreach ($value['equipment'] as $equipment_key => $equipment_value) {
            $json_attr_values['equipment'][$equipment_key]['@btid'] = $equipment_value['@attributes']['btid'];
            $json_attr_values['equipment'][$equipment_key]['@cxid'] = $equipment_value['@attributes']['cxid'];
            $json_attr_values['equipment'][$equipment_key]['@guid'] = $equipment_value['@attributes']['guid'];
            $json_attr_values['equipment'][$equipment_key]['@href'] = $equipment_value['@attributes']['href'];
            $json_attr_values['equipment'][$equipment_key]['@id'] = $equipment_value['@attributes']['id'];
            $json_attr_values['equipment'][$equipment_key]['@language'] = $equipment_value['@attributes']['language'];
            $json_attr_values['equipment'][$equipment_key]['family']['@original'] = $equipment_value['family'];
            $json_attr_values['equipment'][$equipment_key]['family']['#text'] = $equipment_value['family'];
            $json_attr_values['equipment'][$equipment_key]['familygroup']['@original'] = $equipment_value['familygroup'];
            $json_attr_values['equipment'][$equipment_key]['familygroup']['#text'] = $equipment_value['familygroup'];
            $json_attr_values['equipment'][$equipment_key]['manufacturer'] = $equipment_value['manufacturer'];
            $json_attr_values['equipment'][$equipment_key]['manufacturer_original'] = $equipment_value['manufacturer_original'];
            $json_attr_values['equipment'][$equipment_key]['model'] = $equipment_value['model'];
            $json_attr_values['equipment'][$equipment_key]['model_original'] = $equipment_value['model_original'];
            $json_attr_values['equipment'][$equipment_key]['alt_fueltype'] = $equipment_value['alt_fueltype'];
            $json_attr_values['equipment'][$equipment_key]['alt_fueltype_original'] = $equipment_value['alt_fueltype_original'];
            $json_attr_values['equipment'][$equipment_key]['series']['@original'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['series']['#text'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['displacement'] = $equipment_value['series'];
            $json_attr_values['equipment'][$equipment_key]['yearfrom'] = $equipment_value['yearfrom'];
            $json_attr_values['equipment'][$equipment_key]['yearto'] = $equipment_value['yearto'];
            $json_attr_values['equipment'][$equipment_key]['display_year'] = $equipment_value['display_year'];
            $json_attr_values['equipment'][$equipment_key]['display_name_short'] = $equipment_value['display_name_short'];
            $json_attr_values['equipment'][$equipment_key]['fueltype']['@original'] = $equipment_value['fueltype'];
            $json_attr_values['equipment'][$equipment_key]['fueltype']['#text'] = $equipment_value['fueltype'];
            $json_attr_values['equipment'][$equipment_key]['display_name_long'] = $equipment_value['display_name_long'];
          }
        }
      }
    }
    // echo "<pre>";
    // print_r($json_attr_values['equipment']);
    // echo "</pre>";
    // exit;
    //return array();
    return new JsonResponse(['search' => $json_attr_values, 'method' => 'GET', 'status' => 200]);
  }

  public function lubricantsearchform() {	
    $form['form2'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantadvisor\Form\LubricantAdvisorForm2');
    $form['form1'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantadvisor\Form\LubricantAdvisorForm');
    return $form;
  }

 public function lubricantselectoradvisorinfo(){
	\Drupal::service('page_cache_kill_switch')->trigger();
    $parameter = "";
    $html = "";
    $clear_data = '';
    $param = \Drupal::request()->query->all();
    $query_strings = explode('&', $_SERVER['QUERY_STRING']);
	$clear_text = '';
	
	if (!empty($clear_data)) {
      $clear_data .= "&nbsp;<a href='" . $_SERVER['REDIRECT_URL'] . "?q=" . $_GET['q'] . "'>CLEAR ALL</a>";
    }
	$SearchText = isset($_GET['q'])?$_GET['q']:'';
	$config = $this->config('lubricantadvisor.settings');
	$olyslagerApiWsdl = $config->get('olyslagerApiWsdl');		
	$username = $config->get('username');
	$password = $config->get('password');
	
	//Set the soap client and enable tracing for debugging
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
	   "SearchText"=>$SearchText,
	   "CategoryID"=>"",
	   "BuildYear"=>"",
	);
	
	//Call the soap service and store the response.
	$response = $soapClient->GetTypeListFromSearch($parameters);
	//Load the xml with an xml parser
	$xml = simplexml_load_string($response->GetTypeListFromSearchResult->any);
	$array =$type_array = [];$count =0 ;
	//Check if parsing succeed
	if ($xml === false) {
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
		  //foreach($xml->ctdata->resultset->Types as $type) {
			 //Here is the data from this webservice request
			 //print "id: ".$type['id']."; type: ".$type['result'].";\n";
		  //} $array = json_decode(json_encode((array) $xml), 1);
		  $count = $array['ctdata']['resultset']['@attributes']['numrecords'];
		   if($count >1){
			$type_array = $array['ctdata']['resultset']['Types'] ;  
		  }else if($count ==1){
			  $type_array[] = $array['ctdata']['resultset']['Types'] ;
		  }else {
			  $type_array =[];
		  }
		 
	   }
	}
	
	//return new JsonResponse(['type' => $type_array,'count'=>$count, 'method' => 'POST', 'status' => 200]);
	
	
	
	$build['forms'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantadvisor\Form\LubricantAdvisorForm2');
 }
 
 

 
  public function lubricantselecterinfo() {
    \Drupal::service('page_cache_kill_switch')->trigger();
    $parameter = "";
    $html = "";
    $clear_data = '';
    $param = \Drupal::request()->query->all();
    $query_strings = explode('&', $_SERVER['QUERY_STRING']);
	$clear_text = '';
    if (!empty($_SERVER['QUERY_STRING'])) {
      foreach ($query_strings as $key => $value) {
        $parameter .= $value . "&";
        if (mb_strpos($value, 'fueltype') !== false) {
          $clear_text = str_replace('fueltype=', 'Fuel Type: ', $value);
        } elseif (mb_strpos($value, 'year') !== false) {
          $clear_text = str_replace('year=', 'Within Year Range: ', $value);
        } elseif (mb_strpos($value, 'familygroup') !== false) {
          $clear_text = str_replace('familygroup=', 'Sector: ', $value);
        } elseif (mb_strpos($value, 'family') !== false) {
          $clear_text = str_replace('family=', 'Categry: ', $value);
        } elseif (mb_strpos($value, 'series') !== false) {
          $clear_text = str_replace('series=', 'Series: ', $value);
        } elseif (mb_strpos($value, 'displacement') !== false) {
          $clear_text = str_replace('displacement=', 'Engine Size: ', $value);
        } elseif (mb_strpos($value, 'manufacturer') !== false) {
          $clear_text = str_replace('manufacturer=', 'Manufacturer: ', $value);
        }
        if (strrpos($_SERVER['REQUEST_URI'], $value . '&') !== false) {
          $clear_data .= "<a href='" . str_replace($value . '&', '', $_SERVER['REQUEST_URI']) . "'>" . $clear_text . "</a>&nbsp;";
        } elseif (strrpos($_SERVER['REQUEST_URI'], '&' . $value) !== false) {
          $clear_data .= "<a href='" . str_replace('&' . $value, '', $_SERVER['REQUEST_URI']) . "'>" . $clear_text . "</a>&nbsp;";
        }
      }
    }
    if (!empty($clear_data)) {
      $clear_data .= "&nbsp;<a href='" . $_SERVER['REDIRECT_URL'] . "?q=" . $_GET['q'] . "'>CLEAR ALL</a>";
    }
    $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/search?" . $parameter . "token=ldWMYvB1ryWu";
    $client = \Drupal::httpClient();
    $request = $client->get($url, [
      'headers' => [
        'Content-Type' => 'application/xml',
      ],
      'timeout' => 10000,
    ]);
    $json_string = (string) $request->getBody();
    $fileContents = str_replace(array("\n", "\r", "\t"), '', $json_string);
    $fileContents = trim(str_replace('"', "'", $fileContents));
    $simpleXml = simplexml_load_string($fileContents);
    $attr_item_Data = json_decode(json_encode((array) $simpleXml), TRUE);
    $build['forms'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantadvisor\Form\LubricantAdvisorForm2');
    
//echo '<pre>';var_dump($attr_item_Data); echo '</pre>';
    $page_data = $attr_item_Data['equipment_list']['@attributes'];
    $start = ($page_data['page_size'] * ($page_data['page'] - 1)) + 1;
    $end = $page_data['page_size'] * $page_data['page'];
    $str_replace = str_replace('page=' . $page_data['page'] . '&', '', $parameter);
    $url_parameter = $str_replace . 'page=' . ($page_data['page'] + 1);
    $next_page = '<a href="search_lubricant?' . $url_parameter . '">Next Page</a>';
    if ($end > $page_data['total_matches']) {
      $end = $page_data['total_matches'];
      $next_page = '';
    }
    $html .= '<div class="container"><div class="row">
    <div class="col-md-11 offset-md-1 libricant-finder">
      <h1>Search Results</h1>
      <p class="count">Showing ' . $start . ' - ' . $end . ' of ' . $page_data['total_matches'] . ' results. ' . $next_page . '</p>';
    $html .= $clear_data;
    $bucket_value = 1; $selecter_name = '';
    if (isset($attr_item_Data['facets']['facet'])) {
      if (count($attr_item_Data['facets']['facet']) > 0) {
        foreach ($attr_item_Data['facets']['facet'] as $facet_key => $facet_value) {
          //echo '<pre>';var_dump($facet_value); echo '</pre>';
          if(isset($facet_value['@attributes']['name'])){
            $selecter_name = $facet_value['@attributes']['name'];
          }
          
          if ($selecter_name!= 'familygroup') {

            if ($selecter_name == 'fueltype') {
              $selecter_name = 'fuel';
              $option_empty_name = 'Fuel Type';
            } elseif ($selecter_name == 'year') {
              $option_empty_name = 'Within Year Range';
            } elseif ($selecter_name == 'familygroup') {
              $option_empty_name = 'Sector';
            } elseif ($selecter_name == 'family') {
              $option_empty_name = 'Categry';
            } elseif ($selecter_name == 'series') {
              $option_empty_name = 'Series';
            } elseif ($selecter_name == 'displacement') {
              $option_empty_name = 'Engine Size';
            } elseif ($selecter_name == 'manufacturer') {
              $option_empty_name = 'Manufacturer';
            }
            if (!empty($facet_value['bucket'])) {
              if ($bucket_value) {
                $html .= "<div class='selSearch d-none'><form id='lubricant_selector' name='lubricant_selector' method='get' action='/'>";
              }
              $html .= '<div class="facet"><select id="' . $selecter_name . '" class="facet_dropdown ' . $selecter_name . '" name="' . $selecter_name . '">';
              $html .= '<option value="">' . $option_empty_name . '</option>';
              foreach ($facet_value['bucket'] as $bucket_key => $bucket_value) {
                $html .= '<option value="/search_lubricant?' . $parameter . $facet_value['@attributes']['name'] . '=' . $bucket_value['@attributes']['value'] . '">' . $bucket_value['@attributes']['value'] . '</option>';
              }
              $html .= '</select></div>';
              $bucket_value = 0;
            }
          }
        }
        if (!$bucket_value) {
          $html .= '</form></div>';
        }
      }
    }

    if (isset($attr_item_Data['equipment_list']['equipment'])) {
      $html .= '<div class="table-content"><div class="table-responsive"><table class="table table-striped"><thead>
      <tr>
  <th class="category" width="20%">Category</th>
  <th class="manufacturer" width="20%">Manufacturer</th>
  <th class="model" width="10%">Model</th>
  <th class="year" width="10%">Year</th>
  <th class="fuel" width="10%">Fuel</th>
      </tr>
    </thead>';
      $html .= '<tbody>';
      foreach ($attr_item_Data['equipment_list']['equipment'] as $equipment_key => $equipment_value) {
        $family = isset($equipment_value['family']) ? $equipment_value['family'] : '';
        $manufacturer = isset($equipment_value['manufacturer']) ? $equipment_value['manufacturer'] : '';
        $model = isset($equipment_value['model']) ? $equipment_value['model'] : '';
        $display_year = isset($equipment_value['display_year']) ? $equipment_value['display_year'] : '';
        $alt_fueltype = isset($equipment_value['alt_fueltype']) ? $equipment_value['alt_fueltype'] : '';
        $html .= '<tr><td>' . $family . '</td>';
        $html .= '<td>' . $manufacturer . '</td>';
        $html .= '<td><a href="' . $equipment_value['@attributes']['href'] . '">' . $model . '</a></td>';
        $html .= '<td>' . $display_year . '</td>';
        $html .= '<td>' . $alt_fueltype . '</td></tr>';
      }
      $html .= '</tbody></table><div class="text-center"><a href="/lubricantadvisor/form" class="btn-search"> <span>New Search</span></a></div></div></div></div></div></div>';
    } else {
      $html = '<p><a href="/lubricantadvisor/form">Clear Search</a></p><p>No search results found.</p></div></div></div>';
    }
    $build['markup'] = [
      '#markup' => Markup::create($html),
      '#type' => 'markup',
      '#attached' => ['library' => ['lubricantadvisor/lubricant_js']],
    ];

    return $build;
  }
  public function openMailPopupForm($equipment_name = '') {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\lubricantadvisor\Form\MailPopupForm',$equipment_name);

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('Send Mail', $modal_form, ['width' => '800']));

    return $response;
  }
}
