
<table width="100%" border="1"><thead ><tr><th width="22%">Application</th><th width="22%">Products</th><th width="22%">Capacity (litres)</th><th width="22%">Change Intervals</th><th width="22%">Notes</th></tr></thead>
	<tr><td width="22%">Application</td><td width="22%">Products</th><td width="22%">Capacity (litres)</td><td width="22%">Change Intervals</td><td width="22%">Notes</td></tr>




$html .= "<tr><th>" . $value['display_name'] . "</th></tr><tr><td>";
      if (isset($value['product']['name'])) {
        $html .= "<b>Recommendation</b><br/>";
        $html .= $value['product']['@attributes']['tier_name'] . " " . $value['product']['name'] . "<br/>";
      } elseif (isset($value['product'])) {
        foreach ($value['product'] as $product_key => $product_value) {
          if ($product_key == 0) {
            $html .= "<b>Recommendation</b><br/>";
          }
          $html .= $product_value['@attributes']['tier_name'] . " " . $product_value['name'] . "<br/>";
        }
      }
      if (isset($value['display_capacity'])) {
        $html .= "<b>Capacity</b><br/>";
        $html .= $value['display_capacity'] . " (ltr)<br/>";
      }
      if (isset($value['note_ref']['@attributes'])) {
        $html .= "<b>Notes</b><br/>";
        if (is_array($attr_item_Data['equipment']['app_note'])) {
          $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . "<br/>";
          $note_start_index++;
        } else {
          $html .= $value['note_ref']['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'] . "<br/>";
        }
      } elseif (isset($value['note_ref'])) {
        $html .= "<b>Notes</b><br/>";
        foreach ($value['note_ref'] as $note_key => $note_value) {
          $html .= $note_value['@attributes']['noteindex'] . ". " . $attr_item_Data['equipment']['app_note'][$note_start_index] . "<br/>";
          $note_start_index++;
        }
      }
      $html .= "</td></tr>