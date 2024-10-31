<?php


class RDP_LIG_Utilities {
    public static $sessionIsValid = false;
    private static $_MysteryPicURL = '';
    private static $_MysteryGroupPicURL = '';
    
    static function getClientIP() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    static function fetch($method, $resource, $access_token,$inputs = null)
    {
        $params = array('oauth2_access_token' => $access_token,
                        'format' => 'json',
                  );
        
        if(is_array($inputs))$params = array_merge($params, $inputs);

        // Need to use HTTPS
        $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);

        $response = wp_remote_get( $url );
        return $response;
    }  //fetch
    
    static function fetch2($resource, $access_token,$inputs = null)
    {
        $params = "oauth2_access_token=$access_token&format=json";
        // Tell streams to make a (GET, POST, PUT, or DELETE) request 
      
        if(!empty($inputs))$params .= '&' . $inputs;

        // Need to use HTTPS
        $url = 'https://api.linkedin.com' . $resource . '?' . $params;

        $response = wp_remote_get( $url );
        return $response;
    }  //fetch2
    
    static function renderTokenExpiredMessage(){
        $sHTML = '<div id="comment-response-container" class="alert">Your LinkedIn session has expired. Please sign out, and then sign in again with LinkedIn, to view the content.</div>';
        
        return $sHTML;
    }//renderTokenExpiredMessage
    
    static function handleUserRegistration($user){
        $userID = username_exists( $user->emailAddress );
        $userID = apply_filters( 'rdp_lig_before_insert_user', $userID, $user );
        if (is_numeric($userID)){
            update_user_meta($userID, 'rdp_lig_public_profile_url', $user->publicProfileUrl);
            update_user_meta($userID, 'rdp_lig_picture_url', $user->pictureUrl);            
            return true;
        }

        $userdata = array(
            'user_login'    =>  $user->emailAddress,
            'user_pass'  => wp_generate_password( $length=8, $include_standard_special_chars=false ),
            'first_name'    =>  $user->firstName,
            'last_name'    =>  $user->lastName,
            'user_email' => $user->emailAddress,
            'user_url'  =>  $user->publicProfileUrl,           
            'show_admin_bar_front' => 'false',
            'display_name' => trim($user->firstName.' '.$user->lastName)
            );            
        $userID = wp_insert_user($userdata);
        $RV = false;
        //On success
        if( !is_wp_error($userID) ) {
            $wp_user = get_user_by( 'id', $userID );
            update_user_meta($userID, 'rdp_lig_public_profile_url', $user->publicProfileUrl);
            update_user_meta($userID, 'rdp_lig_picture_url', $user->pictureUrl);
            $RV = true;
            do_action('rdp_lig_after_insert_user', $wp_user);
        }         
        return $RV;
    }//handle_user_registration
    
    static function handleRegisteredUserSignOn($user){
        $fLoggedIn = is_user_logged_in();
        $sUserEmail =  $user->emailAddress;
        $fLoggedIn = apply_filters( 'rdp_lig_before_registered_user_login', $fLoggedIn, $sUserEmail );
        if ($fLoggedIn) return;

        $wp_user = get_user_by( 'email', $sUserEmail );
        if( !$wp_user )$wp_user = get_user_by( 'login', $sUserEmail);
        if( $wp_user ) {
            wp_set_current_user( $wp_user->ID, $wp_user->user_login );
            wp_set_auth_cookie($wp_user->ID, false);
            do_action( 'wp_login', $wp_user->user_login );
            do_action('rdp_lig_after_registered_user_login', $wp_user);  
        }else do_action('rdp_lig_registered_user_login_fail',$user);

    }//handleRegisteredUserSignOn
    
    static function tweakAdminBar() {
        global $wp_admin_bar;
        $logout_node = $wp_admin_bar->get_node( 'logout' );
        $wp_admin_bar->remove_node( 'logout' );
        $rdpingroupsid = 0;
        $rdpingroupspostid = 0;
        foreach($_GET as $query_string_variable => $value) {
            if($query_string_variable == 'rdpingroupsid')$rdpingroupsid = $value;
            if($query_string_variable == 'rdpingroupspostid')$rdpingroupspostid = $value;
        }
        $params = RDP_LIG_Utilities::clearQueryParams();
        if(!empty($rdpingroupsid))$params['rdpingroupsid'] = $rdpingroupsid;
        if(!empty($rdpingroupspostid))$params['rdpingroupspostid'] = $rdpingroupspostid;
        $params['rdpingroupsaction'] = 'logout';
        $url = add_query_arg($params);
        $logout_node->href = $url;
        $wp_admin_bar->add_node( $logout_node);
    } //tweakAdminBar 
    
     static function mysteryPicUrl(){
         if(empty(self::$_MysteryPicURL)){
             self::$_MysteryPicURL = plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/pl/images/ghost_person_60x60_v1.png');
         }
         return self::$_MysteryPicURL;
     }//mysteryPicUrl
     
     static function mysteryGroupPicUrl(){
         if(empty(self::$_MysteryGroupPicURL)){
             self::$_MysteryGroupPicURL = plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/pl/images/mystery-group.jpg');
         }
         return self::$_MysteryGroupPicURL;
     }//mysteryPicUrl

     
     static function wsMemberInfoUrl($memberID,$Datapass){
        static $nonce;
        
        if (!isset($nonce))$nonce = wp_create_nonce( 'rdp-lig-member-info-'.$Datapass->key() );
        $params = array('id' => $memberID,'key' => $Datapass->key(),'security' => $nonce);
        return plugins_url(dirname(RDP_LIG_PLUGIN_BASENAME) . '/ws/memberInfo.php?'. http_build_query($params));
     }//wsMemberInfoUrl
     
     
     
     static function bitlyShorten_ajax(){
        $key = (isset($_POST['key']))? $_POST['key'] : '';
        check_ajax_referer( 'rdp-lig-shorten-url-'.$key, 'security' );
        $url = (isset($_POST['url']))? $_POST['url'] : '';
        $options = get_option( 'rdp_lig_options' );
        $text_string = empty($options['sLIGBitlyAccessToken'])? '' : $options['sLIGBitlyAccessToken'];         
        $RV = '';
        if(!empty($url) && !empty($text_string))$RV = self::bitlyShorten ($url, $text_string);
        echo $RV;
        die;
     }//bitlyShorten_ajax
     
     static function bitlyShorten($urlIn,$accessToken){
        $params = array('access_token' => $accessToken,
                        'longUrl' => $urlIn,
                  );

        // Need to use HTTPS
        $url = 'https://api-ssl.bitly.com/v3/shorten?' . http_build_query($params);         
        $RV = '';
        $response = wp_remote_get( $url ); 
        $data = wp_remote_retrieve_body($response);
        $JSON = json_decode($data);
        if(property_exists($JSON, 'status_code') && $JSON->status_code == '200')$RV = $JSON->data->url;
        return $RV;
     }//bitlyShorten


     
    //@param string $text String to truncate.
    //@param integer $length Length of returned string, including ellipsis.
    //@param string $ending Ending to be appended to the trimmed string.
    //@param boolean $exact If false, $text will not be cut mid-word
    //@param boolean $considerHtml If true, HTML tags would be handled correctly
    //@return string Trimmed string.     
    static function truncateString($text, $length = 100, $ending = ' ...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
                    if (!empty($line_matchings[1])) {
                            // if it's an "empty element" with or without xhtml-conform closing slash
                            if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                                    // do nothing
                            // if tag is a closing tag
                            } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                                    // delete tag from $open_tags list
                                    $pos = array_search($tag_matchings[1], $open_tags);
                                    if ($pos !== false) {
                                    unset($open_tags[$pos]);
                                    }
                            // if tag is an opening tag
                            } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                                    // add tag to the beginning of $open_tags list
                                    array_unshift($open_tags, strtolower($tag_matchings[1]));
                            }
                            // add html-tag to $truncate'd text
                            $truncate .= $line_matchings[1];
                    }
                    // calculate the length of the plain text part of the line; handle entities as one character
                    $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                    if ($total_length+$content_length> $length) {
                            // the number of characters which are left
                            $left = $length - $total_length;
                            $entities_length = 0;
                            // search for html entities
                            if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                                    // calculate the real length of all entities in the legal range
                                    foreach ($entities[0] as $entity) {
                                            if ($entity[1]+1-$entities_length <= $left) {
                                                    $left--;
                                                    $entities_length += strlen($entity[0]);
                                            } else {
                                                    // no more characters left
                                                    break;
                                            }
                                    }
                            }
                            $s = substr(trim($line_matchings[2]), 0, 4);
                            if($s == 'http'){
                                $truncate .= $line_matchings[2];
                            }else{
                                $truncate .= substr($line_matchings[2], 0, $left+$entities_length);
                            }
                            
                            // maximum lenght is reached, so get off the loop
                            break;
                    } else {
                            $truncate .= $line_matchings[2];
                            $total_length += $content_length;
                    }
                    // if the maximum length is reached, get off the loop
                    if($total_length>= $length) {
                            break;
                    }
            }
	} else {
            if (strlen($text) <= $length) {
                    return $text;
            } else {
                    $truncate = substr($text, 0, $length - strlen($ending));
            }
	}


	if($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                    $truncate .= '</' . $tag . '>';
            }
	}
        
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
	}        
	// add the defined ending to the text
	$truncate .= $ending;
        return $truncate;
    }       

    
    static function timeDifference($timestamp){
	$timestamp =(int)$timestamp;
	$endtime = time()-$timestamp;
        $diff['years'] = (int)(date("Y",$endtime)-1970);
        $diff['months'] = (int)(date("n",$endtime)-1);
        $diff['days'] = (int)(date("j",$endtime)-1);
        $diff['hours'] = (int)date("G",$endtime);
        $diff['mins'] = (int)date("i",$endtime);
        $diff['secs'] = (int)date("s",$endtime);
        $tense = "ago";
        $difference = '';
        $period = '';
        $sMsg = '';

        if($diff['years'] >= 1){
                $date = new DateTime("@$timestamp");
                $sMsg = $date->format('F j, Y') . "";
        }else{
            foreach($diff as $key => &$val){
                if($val != 0){
                    $period = $key;
                    if($val == 1) $period = substr($period, 0, strlen($period)-1);
                    $difference = $val;
                    $sMsg = "$difference $period $tense";
                    break;
                }
            }//foreach	
        }

        $diff['message'] = $sMsg;
        return json_decode(json_encode($diff));
    }//timeDifference    
  
    static function xmlEntities($string) { 
       return str_replace ( array ( '&', '"', "'", '<', '>', '�' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string ); 
    }  
    
    static function entitiesPlain($string){
        return str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&quest;',  '&#39;' ), array ( '&', '"', "'", '<', '>', '?', "'" ), $string ); 
    }    
    
    static function isImage($url){
        $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif", "bmp");
        $urlExt = pathinfo($url, PATHINFO_EXTENSION);
        return in_array($urlExt, $imgExts);
  }//isImage
  
    static function isScriptStyleImgRequest(){
        $url = (isset($_SERVER['REQUEST_URI']))? $_SERVER['REQUEST_URI'] : '';        
        $imgExts = array("js", "css","gif", "jpg", "jpeg", "png", "tiff", "tif", "bmp");
        $url_parts = parse_url($url);        
        $path = (empty($url_parts["path"]))? '' : $url_parts["path"];
        $urlExt = pathinfo($path, PATHINFO_EXTENSION);
        return in_array($urlExt, $imgExts);
    }//isScriptStyleImgRequest    
  
    static function pluginIsActive($input){
        $active = get_option('active_plugins');
        $active = implode(",", $active);
        $rv = false;
        switch ($input){
            case "we": 
                if (strpos($active, "rdp-wiki-press-embed") !== FALSE) $rv = true;
                break;             
            case "bp": 
                if (strpos($active, "buddypress") !== FALSE) $rv = true;
                break;             
        }//switch
        
       return $rv; 
    }//PluginIsActive   
    
    static function clearQueryParams(){
        $arr_params = array();
        foreach($_GET as $query_string_variable => $value) {
            if(substr($query_string_variable, 0, 11) == 'rdpingroups')$arr_params[$query_string_variable] = false;
            if( $query_string_variable == 'wikiembed-override-url')$arr_params[$query_string_variable] = false;
         }
         return $arr_params;
    }//clearQueryParams
    
    public static function globalRequest( $name, $default = '' ) {
        $RV = '';
        $array = $_GET;

        if ( isset( $array[ $name ] ) ) {
                $RV = $array[ $name ];
        }else{
            $array = $_POST;
            if ( isset( $array[ $name ] ) ) {
                    $RV = $array[ $name ];
            }                
        }
        
        if(empty($RV) && !empty($default)) return $default;
        return $RV;
    }//globalRequest
    
}//RDP_LIG_Utilities

/* EOF */
