<?php

class RbgeGeoTagSootheForm{
    
    public function render($params){
        
        wp_enqueue_script( 'rbge_geo_tag_google_maps' );
        wp_enqueue_script( 'rbge_geo_tag_main_script' );
        
        $out = '';
        
        if ( isset( $_POST['soothe-form-submitted'] ) ) {
            $out .= $this->saveForm();
        }
        
        
        $out .= $this->showForm();
        
        return $out;

    }
    
    
    private function showForm(){
        
        $out = '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="POST">';
        
        // Pixie
        $out .= '<p>';
        $out .= '<strong>Data Pixie</strong> (Your name)';
        $out .= '<input type="text" name="soothe-form-pixie" required="required" value="' . @$_SESSION['data-pixie'] . '" size="40" />';
        $out .= '</p>';
        
        // how often
        $out .= '<p>';
        $out .= '<strong>Visit Frequency:</strong> ';
        $out .= '<input type="radio" name="soothe-form-frequency" value="3" /> Frequently | ';
        $out .= '<input type="radio" name="soothe-form-frequency" value="3"  /> Occasional | ';
        $out .= '<input type="radio" name="soothe-form-frequency" value="1"  /> First Time | ';
        $out .= '<input type="radio" name="soothe-form-frequency" value="-1" checked="checked" /> N/A';
        $out .= '</p>';
        
        // age group
        $out .= '<p>';
        $out .= '<strong>Age:</strong> ';
        $out .= '<input type="radio" name="soothe-form-age" value="1" /> Under 16 | ';
        $out .= '<input type="radio" name="soothe-form-age" value="2"  /> 16-25 | ';
        $out .= '<input type="radio" name="soothe-form-age" value="3"  /> 26-40 | ';
        $out .= '<input type="radio" name="soothe-form-age" value="4"  /> 41-60 | ';
        $out .= '<input type="radio" name="soothe-form-age" value="5"  /> 61-80 | ';
        $out .= '<input type="radio" name="soothe-form-age" value="6"  /> 80+ | ';
        $out .= '<input type="radio" name="soothe-form-age" value="-1" checked="checked" /> N/A';
        $out .= '</p>';
        
        // sex
        $out .= '<p>';
        $out .= '<strong>Sex:</strong> ';
        $out .= '<input type="radio" name="soothe-form-sex" value="Male" /> Male | ';
        $out .= '<input type="radio" name="soothe-form-sex" value="Female"  /> Female | ';
        $out .= '<input type="radio" name="soothe-form-sex" value="N/A" checked="checked" /> N/A';
        $out .= '</p>';
        
        // location
        $out .= '<p>';
        $out .= '<strong>Location</strong>';
        $out .= '<input type="text" name="soothe-form-location" size="40" />';
        $out .= '</p>';
        
        // panels
        $out .= '<p>';
        $out .= '<strong>Panels</strong> (1 or 2 digit numbers only)';
        $out .= '<br/>1: <input type="text" name="soothe-form-panel1" size="4" pattern="[0-9]{1,2}" />';
        $out .= '<br/>2: <input type="text" name="soothe-form-panel2" size="4" pattern="[0-9]{1,2}" />';
        $out .= '<br/>3: <input type="text" name="soothe-form-panel3" size="4" pattern="[0-9]{1,2}" />';
        $out .= '</p>';
        
        // panels- text
        $out .= '<p>';
        $out .= '<strong>Panel Text</strong>';
        $out .= '<textarea type="text" name="soothe-form-panel-text" size="40" ></textarea>';
        $out .= '</p>';
        
        // my well being
        $out .= '<table style="text-align:center;">';
        
        $out .= '<tr>';
        $out .= '<th>&nbsp;</th>';
        $out .= '<th>1</th>';
        $out .= '<th>2</th>';
        $out .= '<th>3</th>';
        $out .= '<th>4</th>';
        $out .= '<th>5</th>';
        $out .= '<th>6</th>';
        $out .= '<th>N/A</th>';
        $out .= '</tr>';
        
        $out .= '<tr>';
        $out .= '<td style="text-align:right;"><strong>MY well-being:</strong></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="1" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="2" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="3" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="4" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="5" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="6" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-my-well-being" value="-1" checked="checked" /></td>';
        $out .= '</tr>';
        
        // everyone's well being
        $out .= '<tr>';
        $out .= '<td  style="text-align:right;" ><strong>EVERYONE\'s well-being:</strong></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="1" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="2" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="3" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="4" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="5" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="6" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-everyone-well-being" value="-1" checked="checked" /></td>';
        $out .= '</tr>';
        
        // soothed me
        $out .= '<tr>';
        $out .= '<td  style="text-align:right;"><strong>Visit Soothed me:</strong></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="1" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="2" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="3" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="4" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="5" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="6" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-soothed-me" value="-1" checked="checked" /></td>';
        $out .= '</tr>';
        
        // how interconnected
        $out .= '<tr>';
        $out .= '<td  style="text-align:right;"><strong>Nature Connection:</strong> ';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="1" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="2" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="3" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="4" /></td>';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="5" /></td>';
        $out .= '<td>&nbsp;</td>';
        $out .= '<td><input type="radio" name="soothe-form-connected" value="-1" checked="checked" /></td>';
        $out .= '</tr>';
        $out .= '</table>';
        
        $out .= '<p></p>';
        
        // email
        $out .= '<p>';
        $out .= '<strong>Email</strong>';
        $out .= '<input type="email" name="soothe-form-email" size="40" />';
        $out .= '</p>';
        
        $out .= '<p>';
        $out .= '<strong>Include in Draw</strong>: ';
        $out .= '<input type="checkbox" name="soothe-form-draw" value="1" />';
        $out .= '</p>';
        
        $out .= '<p>';
        $out .= '<strong>Send results</strong>: ';
        $out .= '<input type="checkbox" name="soothe-form-send-results" value="1" />';
        $out .= '</p>';
        
        $out .= '<p>';
        $out .= '<strong>Future Studies</strong>: ';
        $out .= '<input type="checkbox" name="soothe-form-future-studies" value="1" />';
        $out .= '</p>';
        
        // map- text
        $out .= '<p>';
        $out .= '<strong>Map comments</strong>';
        $out .= '<textarea type="text" name="soothe-form-map-comments" size="40" ></textarea>';
        $out .= '</p>';

        $out .= '<div id="rbge-geo-tag-soothe-map" style="width:100%; height:400px;">MAP GOES HERE</div>';
        $out .= '<input id="rbge-geo-tag-soothe-map-clear" type=button value="Clear Markers">';

        // map points
        $out .= '<p>';
        $out .= '<strong>Map Points</strong>';
        $out .= '<br/><input type="radio" name="soothe-map-select" value="soothe-map-soothed" checked />Soothed: <input type="text" name="soothe-map-soothed" size="40" />';
        $out .= '<br/><input type="radio" name="soothe-map-select" value="soothe-map-anxious" />Anxious: <input type="text" name="soothe-map-anxious" size="40" />';
        $out .= '<br/><input type="radio" name="soothe-map-select" value="soothe-map-excited"  />Excited: <input type="text" name="soothe-map-excited" size="40" />';
        $out .= '</p>';
        
        $out .= '<p><input type="submit" name="soothe-form-submitted" value="Save"></p>';
        
        $out .= '</form>';
        
        return $out;
        
    }
    
    private function saveForm(){
        
        global $wpdb;
        
        $out = '<div style="border:solid 1px black; padding: 1em; margin-bottom: 2em;">';
        
        // save the pixie name in the session so they don't re-enter it
        $_SESSION['data-pixie'] = $_POST['soothe-form-pixie'];
        
        // because I'm stupid enough to have different naming conventions
        $data = array(
            'timestamp' => date("Y-m-d H:i:s"),
            'data_pixie' => $_POST['soothe-form-pixie'],
            'visit_frequency' => $_POST['soothe-form-frequency'], // int
            'age_class' => $_POST['soothe-form-age'], // int
            'sex' => $_POST['soothe-form-sex'], // varchar(10)
            'home' => $_POST['soothe-form-location'], // varchar(100)
            'panel1' => $_POST['soothe-form-panel1'], // int
            'panel2' => $_POST['soothe-form-panel2'], // int
            'panel3' => $_POST['soothe-form-panel3'], // int
            'panel_comment' => $_POST['soothe-form-panel-text'], // varchar(500)
            'importance_my_well_being' => $_POST['soothe-form-my-well-being'], // int
            'importance_everyone_well_being' => $_POST['soothe-form-everyone-well-being'], // int
            'visit_soothed_me' => $_POST['soothe-form-soothed-me'], // int
            'nature_in_me'=> $_POST['soothe-form-connected'], // int
            'email'=> $_POST['soothe-form-email'], // varchar(100)
            'include_in_draw'=> @$_POST['soothe-form-draw'] ? 1:0, // int
            'send_results'=> @$_POST['soothe-form-send-results']  ? 1:0, // int
            'include_in_future_studies'=> @$_POST['soothe-form-future-studies']  ? 1:0, // int
            'map_comments'=> $_POST['soothe-form-map-comments'], // varchar(1000)
            'soothing_places'=> $_POST['soothe-map-soothed'], // varchar(1000)
            'anxious_places'=> $_POST['soothe-map-anxious'], // varchar(1000)
            'exciting_places'=> $_POST['soothe-map-excited'] // varchar(1000)
        );

        $types = array(
            '%s', // timestamp
            '%s', // data_pixie
            '%d', // visit_frequency
            '%d', // age_class
            '%d', // sex
            '%s', // home
            '%d', // panel1
            '%d', // panel2
            '%d', // panel3
            '%s', // panel_comment
            '%d', // importance_my_well_being
            '%d', // importance_everyone_well_being
            '%d', // visit_soothed_me
            '%d', // nature_in_me
            '%s', // email
            '%d', // include_in_draw
            '%d', // send_results
            '%d', // include_in_future_studies
            '%s', // map_comments
            '%s', // soothing_places
            '%s', // anxious_places
            '%s' // exciting_places
        );

        if( $wpdb->insert('rbge_geo_tag_soothe_form', $data, $types) ){
            $out .= '<p>SAVED!</p>';
            $id = $wpdb->insert_id;
            $out .= "<p>Write this number on top right of the questionnaire: " . $id . '</p>';
            // now add them to the heat map list
            $this->save_points($id, 'soothe-map-soothed');
            $this->save_points($id, 'soothe-map-anxious');
            $this->save_points($id, 'soothe-map-excited');
        }else{
            $out .= '<p>Something failed</p>';
            $out .= '<p>Please send Roger this error message:</p>';
            $out .= $wpdb->last_error;
        }
        
        /*
        $out .= '<pre>';
        $out .= print_r($_POST, true);
        $out .= print_r($data, true);
        $out .= '</pre>';
        */
        
        $out .= '</div>';
        
        return $out;
        
    }
    
    private function save_points($id, $mood){
        
        if(!$_POST[$mood]) return;
        
        $points = explode('|', $_POST[$mood]);
        
        foreach($points as $point){
            $p = trim($point);
            if(!$p) continue;
            list($lat, $lon) = explode(',', $p);
            switch ($mood) {
                case 'soothe-map-soothed':
                    $this->save_point($id, $lat, $lon, 9, 5, 5);
                    break;
                case 'soothe-map-excited':
                    $this->save_point($id, $lat, $lon, 5, 9, 5);
                    break;
                case 'soothe-map-anxious':
                    $this->save_point($id, $lat, $lon, 5, 5, 9);
                    break;
            }
            
        }
        
        
    }
    
    private function save_point($id, $lat, $lon, $soothed, $excited, $anxious){
        
        global $wpdb;
        
        $data = array(
            'timestamp' => date("Y-m-d H:i:s"),
            'soothed_slider' => $soothed,
            'excited_slider' => $excited,
            'anxious_slider'=> $anxious,
            'latitude' => $lat,
            'longitude' => $lon,
            'email' => 'soothe-form',
            'memorable_word' => $id,
            'comments' => ''
          );

          $types = array(
            '%s', // timestamp
            '%d', // slider
            '%d', // slider
            '%d', // slider
            '%f', // lat
            '%f', // lon
            '%s', // email
            '%s', // memorable
            '%s' // comments
          );
        
          $wpdb->insert('rbge_geo_tag_soothe', $data, $types);
        
    }
    
    
}

/*

DROP TABLE IF EXISTS `rbge_geo_tag_soothe_form`;
CREATE TABLE `rbge_geo_tag_soothe_form` (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `data_pixie` varchar(20) NOT NULL,
  `visit_frequency` int(8) NOT NULL,
  `age_class` int(8) NOT NULL,
  `sex` int(8) NOT NULL,
  `home` varchar(100) DEFAULT NULL,
  `panel1` int(8) DEFAULT NULL,
  `panel2` int(8) DEFAULT NULL,
  `panel3` int(8) DEFAULT NULL,
  `panel_comment` varchar(1000) DEFAULT NULL,
  `importance_my_well_being` int(8) DEFAULT NULL,
  `importance_everyone_well_being` int(8) DEFAULT NULL,
  `visit_soothed_me` int(8) DEFAULT NULL,
  `nature_in_me` int(8) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `include_in_draw` int(8) DEFAULT NULL,
  `send_results` int(8) DEFAULT NULL,
  `include_in_future_studies` int(8) DEFAULT NULL,
  `map_comments` varchar(1000) DEFAULT NULL,
  `soothing_places` varchar(1000) DEFAULT NULL,
  `anxious_places` varchar(1000) DEFAULT NULL,
  `exciting_places` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

*/

?>