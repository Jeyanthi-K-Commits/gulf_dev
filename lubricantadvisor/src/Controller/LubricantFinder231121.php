<?php
namespace Drupal\lubricantfinder\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

class LubricantFinder extends ControllerBase {
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


  public function lubricantfinderinfo($field_data = '', $field_data1 = '', $field_data2 = '', $field_data3 = '', $field_data4 = '', $field_data5 = '', $field_data6 = '', $field_data7 = '') {
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
    /*echo "<pre>";
    print_r($json_attr_values['equipment']);
    echo "</pre>";
    exit;*/
    //return array();
    return new JsonResponse(['browse' => $json_attr_values, 'method' => 'GET', 'status' => 200]);
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
			  srcset='/" . drupal_get_path('module', 'lubricantfinder') . '/images/hero-lubricant-m.jpg' . "'
			  class='img-fluid'
			/>		 
			<img  src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/hero-lubricant.jpg' . "'  alt='' class='img-fluid' />
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
			
	/* <a id='mail' href='/lubricantfinder/email_popup/".$equipment_name."' class=' email use-ajax button'>Email</a>*/
    $html .= "<div class='hr-line'><hr></div>";
    
    $note_start_index = 0;
    $html .='<div class="col-md-12 pad-0 lubricant_view active">';

    foreach ($attr_item_Data['equipment']['application'] as $key => $value) {

      //echo '<pre>';var_dump($value);echo '</pre>';

      echo 'display name ======='.$value['display_name'];echo '</br>';
      
      $html .= "<div class='row'><div class='col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/minus.png' . "' data-eqcontent='eqcontant".$key."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$key." active'>";
      $html .= "<div class='row'>";     
      if (isset($value['product']['name'])) {
        $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
        $html .= '<div class="product_img">';
        $html .= "<h3 class='heading'>" . $value['product']['name'] . "</h3><br/>";
        if (isset($value['product']['resource'][0]['@attributes']['href'])) {
          $html .= "<img src=\"" . $value['product']['resource'][0]['@attributes']['href'] . "\" ></img>";
        } else {
          //$html .= "<img src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
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
        foreach ($value['product'] as $product_key => $product_value) {
          echo '<pre>';var_dump($product_value);echo '</pre>';
          if (isset($product_value['name'])) {
            $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
            $html .= "<h3 class='heading'>" . $product_value['name'] . "</h3>";
            $html .= '<div class="product_img">';
          }
          /*if (isset($product_value['resource'][0]['@attributes']['href'])) {
            $html .= "<img src=\"" . $product_value['resource'][0]['@attributes']['href'] . "\" ></img>";
          } else {
            $html .= "<img src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
          }*/
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
        $html .= '<div class=\'lubricant-result col-sm-4\'><div class="lubricant-inner-section">';
        /*$html .= '<div class="product_img">';
        $html .= "<img src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
        $html .= "</div>";*/
        if (isset($value['display_capacity'])) {
          $html .= "<div class='additional_info'>";
          $html .= "Capacity: <b>" . $value['display_capacity'] . "Liters</b>";
        } else {
          $html .= "<br/>";
        }
        
        if (isset($value['display_capacity'])) {
          $html .= "</div>";
        }
        $html .= "</div></div>";

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
      
      $html .= "<div class='row'><div class='col-md-6 text-left'><h3 class='equipment_title py-1'>" . $value['display_name'] . "</h3></div><div class='col-md-6 text-end'><img class='expand active' alt='minus' width='30' height='30' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/minus.png' . "' data-eqcontent='eqcontant".$table_eqcontent."' ></div></div><hr class='equipmment_titlebar'><div class='equipment_list eqcontant".$table_eqcontent." active'>";     
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
            $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
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
              $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
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
                  $html.="<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
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
        //$html .= "<img width='100px' height='120px' src='/" . drupal_get_path('module', 'lubricantfinder') . '/images/no_image.png' . "' ></img>";
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

    $html .= '<hr><div class="text-center mb-4"><a href="/lubricantfinder/form" class="btn-search"> <span>New Search</span></a></div>';
    $html .= '</div></div></div>';

    
    // echo "<pre>";
     //print_r($attr_item_Data);
    // echo "</pre>";
    // exit;

    
      
    return array(     
      '#markup' => $html,
      '#attached' => array('library' => array('lubricantfinder/lubricant_js')),
    );
    
    
  }
  
  

  // Search finder
  public function lubricantfindersearch($field_data = '', $field_data1 = '', $field_data2 = '', $field_data3 = '', $field_data4 = '', $field_data5 = '', $field_data6 = '', $field_data7 = '') {
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
    $form['form2'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantfinder\Form\LubricantFinderForm2');
    $form['form1'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantfinder\Form\LubricantFinderForm');
    return $form;
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
    $build['forms'] = \Drupal::formBuilder()->getForm('\Drupal\lubricantfinder\Form\LubricantFinderForm2');
    
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
      $html .= '</tbody></table><div class="text-center"><a href="/lubricantfinder/form" class="btn-search"> <span>New Search</span></a></div></div></div></div></div></div>';
    } else {
      $html = '<p><a href="/lubricantfinder/form">Clear Search</a></p><p>No search results found.</p></div></div></div>';
    }
    $build['markup'] = [
      '#markup' => Markup::create($html),
      '#type' => 'markup',
      '#attached' => ['library' => ['lubricantfinder/lubricant_js']],
    ];

    return $build;
  }
  public function openMailPopupForm($equipment_name = '') {
    $response = new AjaxResponse();

    // Get the modal form using the form builder.
    $modal_form = $this->formBuilder->getForm('Drupal\lubricantfinder\Form\MailPopupForm',$equipment_name);

    // Add an AJAX command to open a modal dialog with the form as the content.
    $response->addCommand(new OpenModalDialogCommand('Send Mail', $modal_form, ['width' => '800']));

    return $response;
  }
}
