<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php

class RDP_LIG {
    private $_key = '';
    private $_datapass = null;
    private $_version;
    private $_options = array();
    private $_discussionID = '';
    
    public function __construct($version,$options){
        $this->_version = $version;
        $this->_options = $options;        

        add_shortcode('rdp-ingroups-group', array(&$this, 'shortcode'));
        add_shortcode('rdp-ingroups-login', array(&$this, 'shortcode_login'));        
        add_shortcode('rdp-ingroups-member-count', array(&$this, 'shortcode_member_count')); 
    }//__construct
    
    function run() {
        if ( defined( 'DOING_AJAX' ) ) return;        
        $fDoingLogOut = (isset($_GET['action']) && $_GET['action'] == 'logout')? true : false;   
        if($fDoingLogOut)return;

        $fLIGRegisterNewUser = isset($this->_options['fLIGRegisterNewUser'])? $this->_options['fLIGRegisterNewUser'] : 'off';
        if($fLIGRegisterNewUser == 'on' && is_user_logged_in()){
            $current_user = wp_get_current_user();
            $this->_key = md5($current_user->user_email);
        }

        if(!has_filter('widget_text','do_shortcode'))add_filter('widget_text','do_shortcode',11);
        $this->_datapass = RDP_LIG_DATAPASS::get($this->_key); 

        if(isset($_GET['rdpingroupsaction']) && $_GET['rdpingroupsaction'] == 'logout'){
            self::handleLogout($this->_datapass);
        }

        if(!$this->_datapass->data_filled()) return;
        if($this->_datapass->tokenExpired()) return;

        $storedIP = $this->_datapass->ipAddress_get();
        $currentIP = RDP_LIG_Utilities::getClientIP();
        $ipVerified = ($storedIP === $currentIP );
        $rdpligrt =  $this->_datapass->sessionNonce_get();
        $rdpligrtAction = 'rdp-lig-read-'.$this->_key; 
        if($rdpligrt === 'new'){
            $rdpligrt = wp_create_nonce( $rdpligrtAction );
            $this->_datapass->sessionNonce_set($rdpligrt);
            $this->_datapass->save();
        }
        $nonceVerified = wp_verify_nonce( $rdpligrt, $rdpligrtAction );
        if($nonceVerified && $ipVerified ){
            RDP_LIG_Utilities::$sessionIsValid = true;
        }
    }//run
    
   
    public function shortcode_member_count($attr){
        $sHTML = '';
        $id = empty($attr['id'])? '':$attr['id'];
        if(!is_numeric($id)) return $sHTML;        
        $link = empty($attr['link'])? '':$attr['link'];
        $prepend = empty($attr['prepend'])? '':$attr['prepend'];
        $prepend = trim($prepend);
        $append = empty($attr['append'])? '':$attr['append'];
        $append = trim($append);

        $sKey = md5('rdp_lig_member_count_'.$id.$link.$prepend.$append);
        if ( false === ( $sHTML = get_transient( $sKey ) ) ) {
            $sURL = 'https://www.linkedin.com/grp/home?gid=' . $id;
            $html = rdp_file_get_html($sURL);
            if(!$html)return $sHTML;
            $oMemberCount = $html->find('div.header .right-entity .member-count',0);
            if(!$oMemberCount)return $sHTML;
            $text = (!empty($prepend))? $prepend . ' ' : '' ;
            $text .= $oMemberCount->plaintext;
            if(!empty($append))$text .=  ' ' . $append;
            $fValidLink = filter_var($link, FILTER_VALIDATE_URL);
            if($fValidLink){
                $sHTML .= "<a class='rdp-lig-member-count' href='{$link}'";
                if(in_array('new', $attr)) $sHTML .= ' target="_blank"';
                $sHTML .= '>';
            }
            $sHTML .= $text;
            if($fValidLink)$sHTML .= '</a>';
            $sHTML = apply_filters( 'rdp_lig_render_member_count', $sHTML, $attr);
            set_transient( $sKey, $sHTML, 1800 );
        }        

        return $sHTML;
    }//shortcode_member_count

    
    public function shortcode_login(){
        if(isset($_GET['rdpingroupsaction']) && $_GET['rdpingroupsaction'] == 'logout')return;
        $fIsLoggedIn = false;
        $token = $this->_datapass->access_token_get();
        if (RDP_LIG_Utilities::$sessionIsValid && !empty($token))$fIsLoggedIn = true;

        $sStatus = ($fIsLoggedIn)? "true":"false";
        
        $sHTML = '';

        if($sStatus == 'false'){
            $sHTML .= '<img style="cursor: pointer;" class="btnLGILogin" src="' . plugins_url( 'images/js-signin.png' , __FILE__ ) . '" > ';
        }else{
            
            $sHTML .= '<a class="rdp-lig-loginout rdp-lig-item logged-in-' . $sStatus . '" aria-haspopup="true" title="My Account">';
            $sHTML .= '<img alt="" src="' . $this->_datapass->pictureUrl_get() . '" class="avatar avatar-26 photo" height="26" width="26"/>';
            $sFName = $this->_datapass->firstName_get();
            if(!empty($sFName))$sHTML .= "Hello, {$sFName}.";
            $sHTML .= '</a>';
            if($this->_datapass->submenuCode_get() == ''):
                $imgSrc = $this->_datapass->pictureUrl_get();
                $fullName = $this->_datapass->fullName_get();
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
               
                $oCustomMenuItems = array();
                $oCustomMenuItems = apply_filters( 'rdp_lig_custom_menu_items', $oCustomMenuItems, $sStatus );
                $sCustomMenuItems = '';
                foreach ($oCustomMenuItems as $key => $value) {
                    $sCustomMenuItems .= '<p><a href="' . $value . '">' . $key . '</a></p>';
                }

                $submenuHTML = <<<EOD
        <div id="rdp-lig-sub-wrapper" class="hidden">
            <div class="rdp-lig-wrap">
            <p>
                <img alt="" src="{$imgSrc}" class="rdp-lig-avatar rdp-lig-avatar-64 photo" height="64" width="64"/>
                <span class="rdp-lig-display-name">{$fullName}</span>
            </p>
            {$sCustomMenuItems}
            <p>
                <a href="{$url}">Sign Out</a>
            </p>
            </div><!-- .rdp-lig-wrap -->
        </div><!-- .rdp-lig-sub-wrapper -->   
   
EOD;
                $this->_datapass->submenuCode_set($submenuHTML);
                add_action('wp_footer', array(&$this,'renderUserActionsSubmenu'));
            endif;
        }

        $this->handleScripts($sStatus, null);
        return apply_filters( 'rdp_lig_render_login', $sHTML, $sStatus );
    }//shortcode_login
    
    public function renderUserActionsSubmenu(){
        echo $this->_datapass->submenuCode_get();
    }//renderUserActionsSubmenu


    public function shortcode($attr){
        if(isset($_GET['rdpingroupsaction']) && $_GET['rdpingroupsaction'] == 'logout')return;

        // Contents of this function will execute when the blogger
        // uses the [rdp-ingroups-group] shortcode.
        $fIsLoggedIn = false;
        $token = $this->_datapass->access_token_get();
        if (RDP_LIG_Utilities::$sessionIsValid && !empty($token))$fIsLoggedIn = true;

        // Get Discussion Post ID
        $this->_discussionID = '';
        if(isset($attr['discussion_id']))$this->_discussionID = $attr['discussion_id'];
        if(isset($_GET['rdpingroupspostid']))$this->_discussionID = $_GET['rdpingroupspostid'];
        
        // Get Discussion Source URL
        $nDiscussionURL = '';
        if(isset($attr['discussion_url']))$nDiscussionURL = $attr['discussion_url'];
        if(isset($_GET['rdpingroupsdiscussionurl']))$nDiscussionURL = $_GET['rdpingroupsdiscussionurl'];
        
        // Get Discussion Comment ID    
        $nCommentID = (isset($_GET['rdpingroupscommentid']))?$_GET['rdpingroupscommentid']:'';

        // Get Group ID
        $nGroupID = 0;
        if(isset($attr['id'])) $nGroupID = preg_replace( '/[^\d]/', '', $attr['id'] );
        $rdpingroupsid = (isset($_GET['rdpingroupsid']))?$_GET['rdpingroupsid']:0;
        if($rdpingroupsid != 0 && $rdpingroupsid != $nGroupID)$nGroupID = $rdpingroupsid;
        if(!is_numeric($nGroupID))$nGroupID = 0;
        
        $sStatus = ($fIsLoggedIn)? "true":"false";
        $this->handleScripts($sStatus);
        $sHeaderTop = $this->renderHeaderTop($sStatus);
        $sHeader = $this->renderHeader($sStatus);
        $sHeaderBottom = $this->renderHeaderBottom($sStatus);
        $sMainContainerHeader = $this->renderMainContainerHeader($sStatus,$nGroupID);
        $sPagingTop = $this->renderPaging($sStatus, 'top');
        $sMainContainer = $this->renderMainContainer($sStatus,$nGroupID,$nDiscussionURL);
        $sPagingBottom = $this->renderPaging($sStatus, 'bottom');
        return $sHeaderTop.$sHeader.$sHeaderBottom.$sMainContainerHeader.$sPagingTop.$sMainContainer.$sPagingBottom;
    }//shortcode

    private function renderMainContainer($status,$groupID,$discussionURL){
        $discussionID = $this->_discussionID;
        $sHTML = wp_nonce_field('rdp-lig-group-comment-'.$this->_key,'commentToken',false,false);
        $sHTML .= wp_nonce_field('rdp-lig-shorten-url-'.$this->_key,'shortenURL',false,false);
        
        $sHTML .= '<input type="hidden" id="txtCurrentAction" name="txtCurrentAction" value=""/>'; 
        $fFromSingle = isset($_GET['rdpingroupsfromsingle'])? $_GET['rdpingroupsfromsingle'] : 0 ;
        $sHTML .= '<input type="hidden" id="txtFromSingle" name="txtFromSingle" value="' . $fFromSingle . '"/>';        
        $sHTML .= '<input type="hidden" id="' . $groupID . '" class="defaultGroupID" value=""/>';
        
        $sLastPostID = isset($_GET['rdpingroupslastpostid'])? $_GET['rdpingroupslastpostid'] : '';
        $sPostID = empty($sLastPostID)? $discussionID : $sLastPostID;
        $sHTML .= '<input type="hidden" id="txtLastDiscussionID" class="txtLastDiscussionID" value="' . $sPostID . '"/>';
        
        $nCurrentPage = isset($_GET['rdpingroupscurrentpage'])? $_GET['rdpingroupscurrentpage'] : 1 ;
        $sHTML .= '<input type="hidden" id="txtCurrentPage" name="txtCurrentPage" value="' . $nCurrentPage . '"/>';

        $sPostsOrder = isset($_GET['rdpingroupspostsorder'])? $_GET['rdpingroupspostsorder'] : 'recent' ;
        $sHTML .= '<input type="hidden" id="txtPostsOrder" name="txtPostsOrder" value="' . $sPostsOrder . '"/>';        
        
        
        $oGroupProfile = RDP_LIG_GROUP_PROFILE::get($groupID);
        if (!$oGroupProfile->dataFilled()) {
            $oGroupProfile->load();
            $oGroupProfile->save();
        }

        $isOpenGroup = ($oGroupProfile->isOpenToNonMembers())? 'true' : 'false';
        $sHTML .= '<input type="hidden" id="txtIsOpenGroup" name="txtIsOpenGroup" value="' . $isOpenGroup . '"/>';         
       
        
        $HTML = '';
        if(!empty($discussionID)){
            $params = array(
               'post_id' => $discussionID,
            );
            wp_localize_script( 'rdp-lig-ajax', 'rdp_lig_single', $params );             
            if(!$oGroupProfile->isOpenToNonMembers()){
                $HTML =  '<div class="alert notice" style="margin-top: 20px;" role="alert"><span></span>This is a private group and discussions are not publicly visible.</div>';
            } else {
                if($status == 'false' && $groupID != 0){
                    $HTML = '<div style="text-align: right;">';
                    $HTML .= $this->shortcode_login();
                    $HTML .= '</div>'; 
                    $HTML .= self::grabDiscussionFromLinkedIn($groupID,$discussionID,$oGroupProfile,$this->_datapass);
                    $HTML .= '<div id="comment-response-container" class="alert">Please ';
                    $HTML .= $this->shortcode_login();
                    $HTML .= ' to view the discussion.</div>';
                    $status .= ' abbreviated';
                }else{

                   $HTML = '<div id="comment-response-container" class="alert" style="display: none;"></div><div class="discussion-post layer" id="' . $discussionID . '"></div><div id="discussion-comments" class="discussion-comments layer"  style="position: relative;"></div><div class="comment-container" style="display: none;"></div>';
                   $HTML .= '<div style="display:none"><div id="data">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</div></div>';
                }                
            }            
        }
        

        $sHTML .= '<div id="rdp-lig-main" class="' . $status . '">';        
        $sHTML .=  $HTML;
	$sHTML .=  '</div><!-- #rdp-lig-main -->';

        $nMultiDiscussionContentWidth = empty($this->_options['nMultiDiscussionContentWidth'])? '84' :$this->_options['nMultiDiscussionContentWidth'] ;
        $nSingleDiscussionCommentWidth = empty($this->_options['nSingleDiscussionCommentWidth'])? '82' : $this->_options['nSingleDiscussionCommentWidth'];
        $sHTML .=  '<style>'."\n";
        $sHTML .=  "#rdp-lig-main .discussion-content{width: {$nMultiDiscussionContentWidth}%;}"."\n";
        $sHTML .=  "#rdp-lig-main .comment-content{width: {$nSingleDiscussionCommentWidth}%;}"."\n";
        $sHTML .=  "#rdp-lig-main .rdp-lig-group-photo-name{width: 75%;}"."\n";
        $sHTML .=  '</style>';
        return $sHTML;
    }//renderMainContainer
    
    public static function grabDiscussionFromLinkedIn($groupID,$discussionID,$oGroupProfile,$Datapass){
        $sHTML = '';
        $discussionURL = sprintf('https://www.linkedin.com/grp/post/%s?json=true', $discussionID);
        $response = wp_remote_get($discussionURL);  

        if ( is_wp_error( $response ) ) {
           return $response->get_error_message();
        } 
        
        $dataPass['code'] = $response['response']['code'] ;
        $dataPass['message'] = $response['response']['message'];        
        if($dataPass['code'] != 200){
            return $dataPass['message'];
        }         
        
        $data = wp_remote_retrieve_body($response);
        $JSON = json_decode($data);          

        $sHTML = RDP_LIG_Discussion::postToHTML($JSON->post, $groupID, $Datapass,$oGroupProfile->isOpenToNonMembers());
        $html = new rdp_simple_html_dom();
        $html->load('<html><body>'.$sHTML.'</body></html>');
        $body = $html->find('body',0);
        foreach ($body->find('a') as $anchor) {
            $anchor->href = null;
            $anchor->class = 'btnLGILogin';
            $anchor->postid = $discussionID;
        }
        $sHTML = $html->find('body',0)->innertext;        
        return $sHTML;
    }//grabDiscussionFromLinkedIn
    
    private function renderMainContainerHeader($status,$groupID){
        $oGroupProfile = RDP_LIG_GROUP_PROFILE::get($groupID);
        
        if (!$oGroupProfile->dataFilled()) {
            $oGroupProfile->load();
            $oGroupProfile->save();
        } 
        
        $fLIGShowManagerChoice = empty($this->_options['fLIGShowManagerChoice'])? 'off' : $this->_options['fLIGShowManagerChoice'];        
        if($fLIGShowManagerChoice != 'off')$status .= ' show-managers-choice';

        $sHTML = '<div id="rdp-lig-main-header" class="rdp-lig-main-header-' . $status . '"><div class="wrap">';
        $sHTML .= '<div id="rdp-lig-top-bar" class="top-bar with-wide-image with-nav">';
        $sHTML .= '<div class="header">';
        if($oGroupProfile->dataFilled() && !$oGroupProfile->hasErrors()){
            global $post;
            $sHTML .= $oGroupProfile->renderHeader($post->ID); 
        }
        $sHTML .= '</div><!-- .header" -->';
        $sHTML .= '</div><!-- #rdp-lig-top-bar" -->';
        
        $sPostsOrder = isset($_GET['rdpingroupspostsorder'])? $_GET['rdpingroupspostsorder'] : 'recent' ;
        if($sPostsOrder == 'popular'){
            $params['rdpingroupspostsorder'] = 'recent';
        }else $params['rdpingroupspostsorder'] = 'popular';

        $params['rdpingroupscurrentpage'] = 1;
        $params['rdpingroupskey'] = $this->_datapass->key();        
        $url = add_query_arg($params); 
        $urlPopular = ($sPostsOrder == 'popular')? '' : 'href="' . $url . '"' ;
        $urlRecent = ($sPostsOrder == 'recency')? '' : 'href="' . $url . '"' ;

        $sHTML .= '<div class="rdp-lig-actions-container" style="display: none;">';
        $sHTML .= '<span class="label">Sort by:</span> <ul class="view-choice-wrapper most-' . $params['rdpingroupspostsorder'] . '" role="navigation"><li class="discussion-view"><a '. $urlRecent . ' class="rdp-lig-posts-order recent" order="recent" title="Go to a page of the most recent discussions">Recent</a></li><li class="discussion-view"><a '. $urlPopular . ' class="rdp-lig-posts-order popular" order="popular" title="Go to a page of the most popular discussions">Popular</a></li></ul>';
        $sHTML .= '</div><!-- .rdp-lig-actions-container --></div><!-- .wrap --></div><!-- #rdp-lig-main-header -->';

        $sHTML = apply_filters( 'rdp_lig_render_main_container_header', $sHTML, $status);
        return $sHTML;
    }//renderMainContainerHeader

    private function renderPaging($status,$location){
        $sLIGPagingStyle = (empty($this->_options))? '' :strtolower($this->_options['sLIGPagingStyle']);
        if(empty($sLIGPagingStyle))$sLIGPagingStyle = 'infinity';
        if($sLIGPagingStyle == 'infinity' && $location == 'top') return;

        $sHTML = '<a class="rdp-lig-paging-link rdp-lig-paging-more rdp-lig-paging-more-' . $location . ' rdp-lig-paging-more-' . $location . '-' . $sLIGPagingStyle . ' show-more-items" rel="next" style="display: none;"><span class="show-more-text">SHOW MORE DISCUSSIONS</span></a>';
        $sHTML .= '<div id="rdp-lig-paging-container-' . $location . '" class="rdp-lig-paging-container rdp-lig-paging-container-' . $sLIGPagingStyle . '" style="display: none;">';
        if($location == 'bottom' && $sLIGPagingStyle == 'full') $sHTML .= '<div id="rdp-lig-paging-message"></div><!-- #rdp-lig-paging-message -->';
        $sHTML .= '<div id="rdp-lig-paging-controls-' . $location . '" class="rdp-lig-paging-controls"><div class="wrap">';
        if($sLIGPagingStyle != 'infinity')$sHTML .= '<a class="rdp-lig-paging-link rdp-lig-paging-previous">Previous Page</a> <span class="rdp-lig-paging-sep">&bull;</span> <a class="rdp-lig-paging-link rdp-lig-paging-next">Next Page</a>';
        $sHTML .= '</div><!-- wrap --></div><!-- .rdp-lig-paging-controls --></div><!-- .rdp-lig-paging-container -->';
        $sHTML = apply_filters( 'rdp_lig_render_paging', $sHTML, $status, $location);

        return $sHTML;
    }//renderPaging
    
    public function scriptsEnqueue(){
        // GLOBAL FRONTEND SCRIPTS
        if(!wp_script_is( 'jquery-url', 'registered' )){
            wp_register_script( 'jquery-url', plugins_url( 'js/url.min.js' , __FILE__ ), array( 'jquery','jquery-query' ), '1.8.6', TRUE);
            wp_enqueue_script( 'jquery-url');
        } 

        wp_enqueue_script( 'rdp-lig-global', plugins_url( 'js/script.global.js' , __FILE__ ), array( 'jquery','jquery-query' ), $this->_version, TRUE);        
        $params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'site_url' => get_site_url()
        );      
        wp_localize_script( 'rdp-lig-global', 'rdp_lig_global', $params );   
        
        
    }//scriptsEnqueue

    private function handleScripts($status){
        if(wp_style_is( 'rdp-lig-style-common', 'enqueued' )) return;
        // LinkedIn CSS
        wp_register_style( 'rdp-lig-style-common', plugins_url( 'style/linkedin.common.css' , __FILE__ ) );
	wp_enqueue_style( 'rdp-lig-style-common' );

        // RDP inGroups+ CSS
        wp_register_style( 'rdp-lig-style', plugins_url( 'style/default.css' , __FILE__ ),array( 'rdp-lig-style-common' ), $this->_version );
        wp_enqueue_style( 'rdp-lig-style' );        
        
        $filename = get_stylesheet_directory() .  '/ingroups.custom.css';
        if (file_exists($filename)) {
            wp_register_style( 'rdp-lig-style-custom',get_stylesheet_directory_uri() . '/ingroups.custom.css',array('rdp-lig-style','rdp-lig-style-common' ) );
            wp_enqueue_style( 'rdp-lig-style-custom' );
        }
        
        $wcrActive = (RDP_LIG_Utilities::pluginIsActive('we'))? 1 : 0 ;
        if($wcrActive){
            $wikiembed_options = get_option( 'wikiembed_options' );
            $wcrActive = empty($wikiembed_options['default']['global-content-replace'])? '0' : $wikiembed_options['default']['global-content-replace'];
            if(!is_numeric($wcrActive))$wcrActive = 0;
        }

        // RDP inGroups+ login script
        wp_enqueue_script( 'rdp-lig-login', plugins_url( 'js/script.login.js' , __FILE__ ), array( 'jquery','jquery-query','jquery-url','rdp-lig-global' ), $this->_version, TRUE);
        $url = get_home_url();
        $params = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'weActive' => $wcrActive,
            'loginurl' => $url 
        );
        wp_localize_script( 'rdp-lig-login', 'rdp_lig_login', $params );
        

        // Position Calculator
        if(!wp_script_is('jquery-position-calculator'))wp_enqueue_script( 'jquery-position-calculator', plugins_url( 'js/position-calculator.min.js' , __FILE__ ), array( 'jquery' ), '1.1.2', TRUE);

        // RDP inGroups+ paging script
        wp_enqueue_script( 'rdp-lig-posts-paging', plugins_url( 'js/script.posts-paging-default.js' , __FILE__ ), array( 'jquery' ), $this->_version, TRUE);
        wp_enqueue_script( 'rdp-lig-comments-paging', plugins_url( 'js/script.comments-paging.js' , __FILE__ ), array( 'jquery' ), $this->_version, TRUE);

        // RDP inGroups+ AJAX script
        wp_enqueue_script( 'rdp-lig-ajax', plugins_url( 'js/script.ajax.js' , __FILE__ ), array( 'jquery','jquery-query','jquery-url','rdp-lig-global'), $this->_version, TRUE);

        $browser = new RDP_LIG_Browser();
        $versionPieces = explode('.', $browser->getVersion());
        $platform = '';
        switch ($browser->getPlatform()) {
            case RDP_LIG_Browser::PLATFORM_APPLE:
                $platform = 'os-mac';
                break;
            case RDP_LIG_Browser::PLATFORM_LINUX:
                $platform = 'os-linux';
                break;
            case RDP_LIG_Browser::PLATFORM_WINDOWS:
                $platform = 'os-win';
                break;            
            default:
                break;
        }

        global $wp_query;

        $params = array(
            'key' => $this->_key,
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'person_id' => $this->_datapass->personID_get(),
            'page_size' => 10,
            'paging_style' => $this->_options['sLIGPagingStyle'],
            'mystery_pic_url' => plugins_url( 'images/ghost_person_60x60_v1.png' , __FILE__ ),
            'browser_name' => $browser->getBrowser(),
            'browser_version' => $versionPieces[0],
            'platform' => $platform,
            'wcr_active' => $wcrActive,
            'wp_post_id' => $wp_query->get_queried_object_id(),
            'logged_in' => $status
        );
        wp_localize_script( 'rdp-lig-ajax', 'rdp_lig', $params );



        do_action( 'rdp_lig_after_scripts_styles');
    }//handleScripts

    private function renderHeader($status) {
        $sHTML = '';

        if($status == 'false'){
            $sHTML = '<img style="cursor: pointer;" class="btnLGILogin" src="' . plugins_url( 'images/js-signin.png' , __FILE__ ) . '" > ';
        }else{
            $sHTML = $this->shortcode_login();
        }

       return apply_filters( 'rdp_lig_render_header', $sHTML, $status );
    } //renderHeader

    private function renderHeaderTop($status) {
        $sHTML = '<div id="rdp-lig-head" class="rdp-lig-head-' . $status . '"><div class="wrap">';
        return apply_filters( 'rdp_lig_render_header_top', $sHTML,$status );
    }//renderHeaderTop

    private function renderHeaderBottom($status) {
        $sHTML = '</div><!-- .wrap --></div> <!-- #rdp-lig-head -->';
        return apply_filters( 'rdp_lig_render_header_bottom', $sHTML, $status);
    }//renderHeaderBottom

    public static function handleLogout($datapass = null){
        if($datapass != null && $datapass->data_filled()){
            RDP_LIG_DATAPASS::delete($datapass->key());            
        }

        $rdpingroupsid = 0;
        $rdpingroupspostid = 0;
        foreach($_GET as $query_string_variable => $value) {
            if($query_string_variable == 'rdpingroupsid')$rdpingroupsid = $value;
            if($query_string_variable == 'rdpingroupspostid')$rdpingroupspostid = $value;
        }
        $params = RDP_LIG_Utilities::clearQueryParams();
        if(!empty($rdpingroupsid))$params['rdpingroupsid'] = $rdpingroupsid;
        if(!empty($rdpingroupspostid))$params['rdpingroupspostid'] = $rdpingroupspostid;
        $url = add_query_arg($params);
        
        // log the user out of WP, as well
        if(is_user_logged_in()){
            $url = wp_logout_url( $url );
        }

        // Hack to deal with 'headers already sent' on Linux servers
        // and persistent browser session cookies
        echo "<meta http-equiv='Refresh' content='0; url={$url}'>";
        ob_flush();
        exit;
    }//handleLogout
    
    
}//class RDP_LIG


/* EOF */
 