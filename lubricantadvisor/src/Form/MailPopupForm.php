<?php
namespace Drupal\lubricantadvisor\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\lubricantadvisor\Controller\LubricantAdvisor;
Use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\AjaxResponse;

/** * ModalForm class. */
class MailPopupForm extends FormBase {

  /**   * {@inheritdoc}   */
  public function getFormId() {
    return 'mail_popup_form';
  }

  /**   * {@inheritdoc}   */
  public function buildForm(array $form, FormStateInterface $form_state, $equipment_name= NULL) {
    $form['#prefix'] = '<div id="modal_example_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    // A required checkbox field.
    $form['To'] = [
      '#type' => 'textfield',
      '#title' => $this->t('To :'),
      '#required' => TRUE,
      
    ];
    $form['Bcc'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Bcc :'),        
      ];
      $form['Subject'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Subject :'),
        '#required' => TRUE,
        '#default_value'=>'Lubricant Finder',
        
      ];

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#attributes' => [
        'class' => [
          'use-ajax', 'node_import',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];
    $form['eqname']=[
        '#type' => 'textfield',        
        '#required' => TRUE,
        '#value'=> $equipment_name,
        '#attributes' => [
            'class' => [
              'hide'              
            ],
          ],
      ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**   * AJAX callback handler that displays any errors or a success message.   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    // If there are any form errors, re-display the form.
    if (!$form_state->hasAnyErrors()) {
     $url = "https://oilco-web-chatham-global.phoenix.earlweb.net/equipment/" . $form_state->getValue('eqname') . "?token=ldWMYvB1ryWu";
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
    $attr_formail = json_decode(json_encode((array) $simpleXml), TRUE);

  $mail_html='';
    $mail_html.="<html><head><style>
     .mail_table{
        text-align: left;
        border: 1px solid #ccc;  
      }
      tr{
        border: 1px solid #ccc; 
      }
      .mail_text{
        font-weight: bold; 
      }
      
      </style></head><body>";
    $note_start_index=0;
   
    //$mail_html="<div class='mail_text'>";
    $mail_html.="<b>Make </b>".$attr_formail['equipment']['manufacturer']."<br/>";
    $mail_html.="<b>Model </b>".$attr_formail['equipment']['model']."<br/>";
    $mail_html.="<b>Engine </b>".$attr_formail['equipment']['alt_fueltype']."<br/>";
    $mail_html.="<b>Year </b>".$attr_formail['equipment']['display_year']."<br/>";
    //$mail_html="</div>";
    $mail_html .= '<div class="row"><table class="mail_table" width="100%" border="0"><thead ><tr border="0"><th width="20%" >Application</th><th width="65%">Recommandation</th><th width="12%">Capacity (litres)</th></tr></thead><tbody>';
    $note_start_indexs = 0;

        foreach ($attr_formail['equipment']['application'] as $key => $value) {   
          if (isset($value['display_name'])) {
              $mail_html .= "<tr><td>" . $value['display_name']. "</td>";
            } 
            
            //if display name end 
            //2nd column
              if (isset($value['product']['name'])) {             
              //if tire is available (Choice)
              if (isset($value['product']['@attributes']['tier_name'])) {
                $mail_html .= "<td><table><tr>";
                $mail_html.="<td>";
                $mail_html .= $value['product']['@attributes']['tier_name'] . "</td>";
                $mail_html.="<td>";
                $mail_html .= $value['product']['name'] . "</td>";
                $mail_html.="</tr></table></td>";                       
              }
              else
              {
                $mail_html .= "<td>" . $value['product']['name']. "</td>";
              }
              if (isset($value['display_capacity'])) {
                $mail_html .= "<td>" . $value['display_capacity'] . "</td>";
              } else {
                $mail_html .= "<td></td>";
              }
           }
           elseif (isset($value['product'])) {
            $mail_html.="<td><table><tr>";
             foreach ($value['product'] as $product_key => $product_value) {
                
                  if (isset($product_value['@attributes']['tier_name'])) {
                    $mail_html .= "<td><table><tr>";
                    $mail_html.="<td>";
                    $mail_html .= $product_value['@attributes']['tier_name'] . "</td>";
                    $mail_html.="<td>";
                    $mail_html .= $product_value['name'] . "</td>";
                    $mail_html.="</tr></table></td>";                       
                  }
                  else
                  {
                    $mail_html .= "<td>" . $product_value['name']. "</td>";
                  }
                  
                  $mail_html.="</tr>";
                  
              }
              $mail_html.="</table></td>";
              if (isset($value['display_capacity'])) {
                $mail_html .= "<td>" . $value['display_capacity'] . "</td>";
              } else {
                $mail_html .= "<td></td>";
              }
           }
           else{    
            if (isset($value['product']['@attributes']['tier_name'])) {
              $mail_html .= "<td>".$value['product']['@attributes']['tier_name']."</td>";                       
            }
            else
            {
              $mail_html .= "<td></td>";
            }

            if (isset($value['display_capacity'])) {
              $mail_html .= "<td>" . $value['display_capacity'] . "</td>";
            } else {
              $mail_html .= "<td></td>";
            }     
           }
           $mail_html.="</tr>";

        }//For end

        $mail_html .= "</tbody></table></div>";
        $mail_html.="<div><b>General Notes</b></div>";
        //check
        $mail_html.="<div>There are no notes to display for this model</div>";
        $mail_html.="<div><b>Lubricant / Capacity Notes</b></div>";

        $mail_html .= '<div class="row">';
        $mail_html.='<table width="100%"  class="mail_table" border="0"><thead><tr border="0"><th width="20%" ></th><th width="65%"></th></tr></thead><tbody>';
      foreach ($attr_formail['equipment']['application'] as $key => $value) {
        
        if (isset($value['note_ref']['@attributes'])) {     
          $mail_html .= "<tr>";     
          if (is_array($attr_formail['equipment']['app_note'])) {
           $mail_html .= "<td>".$value['note_ref']['@attributes']['noteindex'] . "</td><td>" . $attr_formail['equipment']['app_note'][$note_start_index] . "</td>";
          $note_start_index++;
          } else {
          $mail_html .= "<td>".$value['note_ref']['@attributes']['noteindex'] . "</td><td>" . $attr_formail['equipment']['app_note'] . "</td>";
          }
          $mail_html .= "</tr>";
        } elseif (isset($value['note_ref'])) {          
          foreach ($value['note_ref'] as $note_key => $note_value) {
            $mail_html .= "<tr>";          
            $mail_html .= "<td>". $note_value['@attributes']['noteindex'] . "</td><td>" . $attr_formail['equipment']['app_note'][$note_start_index] . "</td>";
            $note_start_index++;
            $mail_html .= "</tr>";
          }          
        }
        

      }
        $mail_html .= "</tbody></table></div>";
        
        
        $mail_html.="<div class='row'><b><div>Oil Change Intervals</b></div>";
        if (isset($attr_formail['equipment']['change_intervals']['interval'])) {
          $mail_html.="<div>".$attr_formail['equipment']['change_intervals']['@attributes']['application_name']."</div>";
          $mail_html.="<div>".$attr_formail['equipment']['change_intervals']['interval']." Month(s)</div>";

        }
        else{
          $mail_html.="<div>There is no Oil Change Interval data to display for this model</div>";
        }
        $mail_html.="</div></body></html>"; 
        //Need to call popup
        $mailManager = \Drupal::service('plugin.manager.mail');
        $module = 'lubricantadvisor';
        $key = 'Lubricant';
        $to = $form_state->getValue('To');
        $params['message'] = $mail_html;
        $params['subject'] = $form_state->getValue('Subject');
        if($form_state->getValue('Bcc') != ''){
         $params['Bcc'] = $form_state->getValue('Bcc');
        }
        $langcode = \Drupal::currentUser()->getPreferredLangcode();
        $send = true;
        $result = $mailManager->mail($module, $key, $to, $langcode, $params, "", $send);
        if ($result['result'] !== true) {
        \Drupal::messenger()->addMessage(t('There was a problem sending your message and it was not sent.'), 'error');
        }
        else {
        \Drupal::messenger()->addMessage(t('Your message has been sent.'));
        }        
        
      
      $command = new CloseModalDialogCommand();
        $response = new AjaxResponse();
        $response->addCommand($command);
        return $response;

    }

  }

  /**   * {@inheritdoc}   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**   * {@inheritdoc}   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

  /**   * Gets the configuration names that will be editable.   *   * @return array   *   An array of configuration object names that are editable if called in   *   conjunction with the trait's config() method.   */
  protected function getEditableConfigNames() {
    return ['config.modal_form_example_modal_form'];
  }

}
