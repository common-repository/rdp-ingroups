<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php
/**
 * Description of rdpLIGAdminMenu
 *
 * @author Robert
 */
class RDP_LIG_AdminMenu {
    /*------------------------------------------------------------------------------
    Add admin menu
    ------------------------------------------------------------------------------*/
    static function add_menu_item()
    {
        if ( !current_user_can('activate_plugins') ) return;
        add_options_page( 'RDP inGroups+', 'RDP inGroups+', 'manage_options', RDP_LIG_PLUGIN::$plugin_slug, 'RDP_LIG_AdminMenu::generate_page' );

    } //add_menu_item


    /*------------------------------------------------------------------------------
    Render settings page
    ------------------------------------------------------------------------------*/
    static function generate_page()
    {  
	echo '<div class="wrap">';
        echo '<h2>RDP inGroups+</h2>';

        echo '<form action="options.php" method="post">';
        settings_fields('rdp_lig_options');
        do_settings_sections(RDP_LIG_PLUGIN::$plugin_slug); 
        echo '<input name="Submit" type="submit" value="Save Changes" />';
        echo '</form>';
        
        echo '<h3 style="margin-top: 40px;"  class="title">';
        esc_html_e("Usage",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</h3>';
        echo '<p>';
        _e("Add the <b>[rdp-ingroups-group]</b> shortcode to a page and specify a group ID, which will display discussions of the designated group to site visitors: <b>[rdp-ingroups-group id=2069898]</b>. The shortcode will display a <i>Sign in with LinkedIn</i> button if the user is not logged in.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';

        echo '<p>';
        _e("For a display of a group's member count, use the <b>[rdp-ingroups-member-count]</b> shortcode.<br />The <b>id attribute is required</b> and is set to a group ID: <b>[rdp-ingroups-member-count id=209217]</b><br />You can also specify a url to make the member count a hyperlink: <b>[rdp-ingroups-member-count id=209217 link=http://example.com]</b><br />To make the link open in a new tab, add <b>new</b> as a shortcode attribute: <b>[rdp-ingroups-member-count id=209217 link=http://example.com new]</b><br />To add text to the shortcode output, use the <b>prepend</b> and <b>append</b> attributes: <b>[rdp-ingroups-member-count id=209217 link=http://example.com prepend='Join our' append='today!']</b>",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';        

        echo '<p>';
        _e("To display a <i>Sign in with LinkedIn</i> button, add the <b>[rdp-ingroups-login]</b> shortcode to a widget.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        
        echo '<h3 style="margin-top: 40px;"  class="title">';
        esc_html_e("CSS Styling",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</h3>';
        echo '<p>';
        _e("For more control, add a custom.css file. Start with the custom-sample.css file located in the 'rdp-linkedin-groups/pl/style/' directory.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';        
        
        
        echo '<h3 style="margin-top: 40px;" class="title">';
        esc_html_e("Hook Reference",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</h3>';
        echo '</p>';        
        echo '<p><b>rdp_lig_before_user_login</b><br />';
        _e("Param: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id<br />Fires before any user is logged into the site via LinkedIn.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';          
        $sRegNew = _x('<i>Register New Users</i>? must be enabled', 'settings page', RDP_LIG_PLUGIN::$plugin_slug);
        echo '<p><b>rdp_lig_after_insert_user</b><br />';
        _e("Param: WP User Object<br />Fires after a new user is registered with the site. ({$sRegNew})",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_after_registered_user_login</b><br />';
        _e("Param: WP User Object<br />Fires after a registered user is logged into the site. ({$sRegNew})",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_registered_user_login_fail</b><br />';
        _e("Param: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id<br />Fires after a failed attempt to log registered user into the site. ({$sRegNew})",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';        
        echo '<p><b>rdp_lig_after_user_login</b><br />';
        _e("Param: RDP_LIG_DATAPASS object<br />Fires after any user is logged into the site via LinkedIn.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';        
        echo '<p><b>rdp_lig_after_scripts_styles</b><br>';
        _e("Param: None<br />Fires after enqueuing plug-in-specific scripts and styles.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';

        echo '<h3 style="margin-top: 40px;" class="title">';
        esc_html_e("Filter Reference",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</h3>';
        echo '<p><b>rdp_lig_render_header_top</b><br />';
        _e("Param 1: String containing opening div and wrapper HTML for header section<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: opening HTML for header section",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_header</b><br />';
        _e("Param 1: String containing the body HTML for header section<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: body HTML for header section",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_header_bottom</b><br />';
        _e("Param 1: String containing closing wrapper and div HTML for header section<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: closing HTML for header section",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_main_container_header</b><br />';
        _e("Param 1: String containing HTML for main container header section<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: HTML for main container header section<br />Default behavior is to render the group profile logo and name",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_paging</b><br />';
        _e("Param 1: String containing HTML for paging section<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Param 3: String containing the location - 'top' of main container section, 'bottom' of main container section<br />Return: HTML for paging section. For infinity paging, location 'top' is not rendered.",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_login</b><br />';
        _e("Param 1: String containing log-in HTML for the [rdp-ingroups-login] shortcode<br />Param 2: String containing status - 'true' if user is logged in, 'false' otherwise<br />Return: log-in HTML for the [rdp-ingroups-login] shortcode",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_render_member_count</b><br />';
        _e("Param: String containing member count HTML for the [rdp-ingroups-member-count] shortcode",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_before_insert_user</b><br />';
        _e("Param 1: Boolean indicating if user exists based on result of Wordpress username_exists() function, using supplied email address<br />Param 2: JSON object representing a LinkedIn Person containing firstName, lastName, emailAddress, pictureUrl, publicProfileUrl, and id<br />Return: Boolean indicating if user exists",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        echo '<p><b>rdp_lig_before_registered_user_login</b><br />';
        _e("Param 1: Boolean indicating if user is logged in based on result of Wordpress is_user_logged_in() function<br />Param 2: String containing email address of user<br />Return: Boolean indicating if user is logged in",RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
        
        echo '</div>';

    }//generate_page

    static function admin_page_init(){
        if ( !current_user_can('activate_plugins') ) return;
        //Add settings link to plugins page
        add_filter('plugin_action_links', array('RDP_LIG_AdminMenu', 'add_settings_link'), 10, 2);

        register_setting(
            'rdp_lig_options',
            'rdp_lig_options',
            'RDP_LIG_AdminMenu::lig_options_validate'
        );

        // LinkedIn API Section
        add_settings_section(
            'rdp_lig_main',
            esc_html__('LinkedIn API Settings',RDP_LIG_PLUGIN::$plugin_slug),
            'RDP_LIG_AdminMenu::lig_section_text',
            RDP_LIG_PLUGIN::$plugin_slug
	);
        add_settings_field(
            'sLGIAPIKey',
            esc_html__('API Key:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_API_Key_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_main'
        );
        add_settings_field(
            'sLGIAPISecretKey',
            esc_html__('Secret Key:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_API_Secret_Key_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_main'
        );
  
        // URL Shortening Section
        add_settings_section(
            'rdp_lig_url_shorten',
            esc_html__('Bitly API Settings',RDP_LIG_PLUGIN::$plugin_slug),
            'RDP_LIG_AdminMenu::lig_section_url_shortening_text',
            RDP_LIG_PLUGIN::$plugin_slug
	);        

        add_settings_field(
            'sLIGBitlyAccessToken',
            esc_html__('Bitly Generic Access Token:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Bitly_Access_Token_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_url_shorten'
        );        
        

        // inGroups+ Settings
	add_settings_section(
            'rdp_lig_settings',
            esc_html__('inGroups+ Settings',RDP_LIG_PLUGIN::$plugin_slug),
            'RDP_LIG_AdminMenu::lig_section_ingroups_text',
            RDP_LIG_PLUGIN::$plugin_slug
	);
        
        add_settings_field(
            'fLIGRegisterNewUser',
            esc_html__('Register New Users?:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Register_New_Users_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );
        
        add_settings_field(
            'fLIGShowManagerChoice',
            esc_html__('Show Manager\'s Choice?:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Show_Manager_Choice_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );        

        add_settings_field(
            'sLIGPagingStyle',
            esc_html__('Paging Style:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Paging_Style_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );

        add_settings_field(
            'nMultiDiscussionContentWidth',
            esc_html__( 'Discussion Content Width (group view):',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Multi_Discussion_Content_Width_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );

        add_settings_field(
            'nSingleDiscussionCommentWidth',
            esc_html__( 'Comment Content Width (single discussion view):',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Single_Discussion_Comment_Width_Input'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );
        
        add_settings_field(
            'sCompaniesToFollow',
            esc_html__( 'Auto-follow Companies:',RDP_LIG_PLUGIN::$plugin_slug),
            array('RDP_LIG_AdminMenu', 'LIG_Companies_To_Follow'),
            RDP_LIG_PLUGIN::$plugin_slug,
            'rdp_lig_settings'
        );        

    } //admin_page_init
    
    
    static function LIG_Show_Manager_Choice_Input(){
         $options = get_option( 'rdp_lig_options' );
         $fLIGShowManagerChoice = empty($options['fLIGShowManagerChoice'])? 'off' : $options['fLIGShowManagerChoice'];
        echo "<input id='fLIGShowManagerChoice' name='rdp_lig_options[fLIGShowManagerChoice]' type='checkbox' " . checked($fLIGShowManagerChoice, 'on',false) . " />";
        echo '<p>- ';
        esc_html_e("Controls display of Manager's Choice items in discussion lists and RSS feeds.", RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';        
    }//LIG_Show_Manager_Choice_Input
    
    
    static function LIG_Bitly_Access_Token_Input(){
        $options = get_option( 'rdp_lig_options' );
        $text_string = empty($options['sLIGBitlyAccessToken'])? '' : $options['sLIGBitlyAccessToken'];
        $text_string = esc_attr($text_string);
        echo "<input id='txtLIGBitlyAccessToken' name='rdp_lig_options[sLIGBitlyAccessToken]' type='text' value='$text_string' />";
    }//LIG_Bitly_Access_Token_Input
    
    static function LIG_Companies_To_Follow(){
        $options = get_option( 'rdp_lig_options' );
        $text_string = empty($options['sCompaniesToFollow'])? '' : $options['sCompaniesToFollow'];
        $text_string = esc_textarea($text_string); 
        echo '<textarea name="rdp_lig_options[sCompaniesToFollow]"  rows="10" cols="50">' . $text_string . '</textarea>';
        echo '<p>- ';
        _e("List of company IDs to automatically set users as followers. Separate IDs by new lines.", RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br />- ';
        _ex('<i>Register New Users</i>? must be enabled', 'settings page', RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
    }//LIG_Companies_To_Follow
    
   
    static function LIG_Single_Discussion_Comment_Width_Input(){
        $options = get_option( 'rdp_lig_options' );
        $nSingleDiscussionCommentWidth = empty($options['nSingleDiscussionCommentWidth'])? '84' : $options['nSingleDiscussionCommentWidth'] ;

        echo "<select id='nSingleDiscussionCommentWidth' name='rdp_lig_options[nSingleDiscussionCommentWidth]'>";
        echo '<option value="80" '. selected( $nSingleDiscussionCommentWidth, '80', false ) .'>80%</option>';
        echo '<option value="82" '. selected( $nSingleDiscussionCommentWidth, '82', false ) .'>82%</option>';
        echo '<option value="84" '. selected( $nSingleDiscussionCommentWidth, '84', false ) .'>84%</option>';
        echo '<option value="86" '. selected( $nSingleDiscussionCommentWidth, '86', false ) .'>86%</option>';
        echo '<option value="90" '. selected( $nSingleDiscussionCommentWidth, '90', false ) .'>90%</option>';
        echo '</select>';
        echo '<p>- ';
        _e("Controls the width of comment content area when displaying single discussion posts.", RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
    }//LIG_Single_Discussion_Comment_Width_Input

    static function LIG_Multi_Discussion_Content_Width_Input(){
        $options = get_option( 'rdp_lig_options' );
        $nMultiDiscussionContentWidth = empty($options['nMultiDiscussionContentWidth'])? '82' : $options['nMultiDiscussionContentWidth'];

        echo "<select id='nMultiDiscussionContentWidth' name='rdp_lig_options[nMultiDiscussionContentWidth]'>";
        echo '<option value="80" '. selected( $nMultiDiscussionContentWidth, '80', false ) .'>80%</option>';
        echo '<option value="82" '. selected( $nMultiDiscussionContentWidth, '82', false ) .'>82%</option>';
        echo '<option value="84" '. selected( $nMultiDiscussionContentWidth, '84', false ) .'>84%</option>';
        echo '<option value="86" '. selected( $nMultiDiscussionContentWidth, '86', false ) .'>86%</option>';
        echo '<option value="90" '. selected( $nMultiDiscussionContentWidth, '90', false ) .'>90%</option>';
        echo '</select>';
        echo '<p>- ';
        esc_html_e("Controls the width of discussion-list content area.", RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
    }//LIG_Discussion_Content_Width_Multi_Input

    static function LIG_Paging_Style_Input(){
        $options = get_option( 'rdp_lig_options' );
        $sLIGPagingStyle = empty($options['sLIGPagingStyle'])? 'infinity' : $options['sLIGPagingStyle'];

        $html = '<input type="radio" id="sLIGPagingStyle_minimal" name="rdp_lig_options[sLIGPagingStyle]" value="minimal"' . checked( 'minimal', $sLIGPagingStyle, false ) . '/>';
        $html .= '<label for="sLIGPagingStyle_minimal">' . esc_html__('Minimal') . '</label> ';

        $html .= '<input type="radio" id="sLIGPagingStyle_infinity" name="rdp_lig_options[sLIGPagingStyle]" value="infinity"' . checked( 'infinity', $sLIGPagingStyle, false ) . '/>';
        $html .= '<label for="sLIGPagingStyle_infinity">' . esc_html__('Infinity') . '</label>';
        echo $html;
    }//LIG_Paging_Style_Input


    static function LIG_Register_New_Users_Input() {
         $options = get_option( 'rdp_lig_options' );
         $fLIGRegisterNewUser = empty($options['fLIGRegisterNewUser'])? 'on' : $options['fLIGRegisterNewUser'];
        echo "<input id='fLIGRegisterNewUser' name='rdp_lig_options[fLIGRegisterNewUser]' type='checkbox' " . checked($fLIGRegisterNewUser, 'on',false) . " />";
        echo '<p>- ';
        esc_html_e("Register new users with this site after they log in via LinkedIn.", RDP_LIG_PLUGIN::$plugin_slug);
        echo '</p>';
     }

    static function LIG_API_Key_Input(){
        $options = get_option( 'rdp_lig_options' );
        $text_string = empty($options['sLGIAPIKey'])? '' : $options['sLGIAPIKey'];
        $text_string = esc_attr($text_string);

        echo "<input id='txtLGIAPIKey' name='rdp_lig_options[sLGIAPIKey]' type='text' value='$text_string' />";
    }

    static function LIG_API_Secret_Key_Input(){
        $options = get_option( 'rdp_lig_options' );
        $text_string = empty($options['sLGIAPISecretKey'])? '' : $options['sLGIAPISecretKey'];
        $text_string = esc_attr($text_string);

        echo "<input id='txtLGIAPISecretKey' name='rdp_lig_options[sLGIAPISecretKey]' type='text' value='$text_string' />";

    }


    /*------------------------------------------------------------------------------
    Validate incoming data
    ------------------------------------------------------------------------------*/
   static function lig_options_validate($input) {
        return $input;
    } //lig_options_validate

    static function lig_section_text() {
        $sRedirectURL = get_home_url(null,'/rdpingroupsaction/authorize');
        $sLogoURL = 'https://scontent-atl.xx.fbcdn.net/hphotos-xta1/v/t1.0-9/1907798_451709555002422_8223002391085941855_n.jpg?oh=5b36e2863c7b90a53a3753d613768293&oe=55C2C29C';
        echo '<div style="border-left: 4px solid #7ad03a;padding: 1px 12px;background-color: #fff;">';
        esc_html_e('Get a LinkedIn Application API key here', RDP_LIG_PLUGIN::$plugin_slug);
	echo ': <a href="https://www.linkedin.com/secure/developer" target="_new">https://www.linkedin.com/secure/developer</a>';
        echo '<br /><b>';
	esc_html_e('Application Name', RDP_LIG_PLUGIN::$plugin_slug);
	echo ':</b> RDP inGroups+ Plugin';
        echo '<br /><b>';
	esc_html_e('Application Logo URL', RDP_LIG_PLUGIN::$plugin_slug);
        echo ':</b> ' . $sLogoURL; 
        echo '<br /><b>';        
	esc_html_e('Description', RDP_LIG_PLUGIN::$plugin_slug);
        echo ':</b> ';
	esc_html_e('Integrate LinkedIn with WordPress',RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br /><b>';
	esc_html_e('Application Use', RDP_LIG_PLUGIN::$plugin_slug);
	echo ':</b> ';
	esc_html_e('Social Aggregation', RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br /><b>';
	esc_html_e('Live Status', RDP_LIG_PLUGIN::$plugin_slug);
        echo ':</b> ';
	esc_html_e('Live', RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br /><b>';
	esc_html_e('Default Application Permissions', RDP_LIG_PLUGIN::$plugin_slug);
        echo ':</b> ';
	esc_html_e('tick r_basicprofile and r_emailaddress', RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br /><b>';
	esc_html_e('OAuth 2.0 Redirect URL', RDP_LIG_PLUGIN::$plugin_slug);
        echo ':</b> ' . $sRedirectURL;
        echo '</div>';
    }

    static function lig_section_ingroups_text() {
        echo '';
    }
    
    static function lig_section_url_shortening_text() {
        echo '<p>';
        esc_html_e('Access to Bitly URL shortening service is required to activate social sharing and syndication functionality for individual discussions.', RDP_LIG_PLUGIN::$plugin_slug);
        echo '<br>';
        esc_html_e('Get a Bitly Generic Access Token here', RDP_LIG_PLUGIN::$plugin_slug);
        echo ': <a href="https://bitly.com/a/oauth_apps" target="_new">https://bitly.com/a/oauth_apps</a>';
        echo '</p>';
        
    }
    /**
     * Add Settings link to plugins page
     */
    static function add_settings_link($links, $file) {
        if ($file == RDP_LIG_PLUGIN_BASENAME){
        $settings_link = '<a href="options-general.php?page=' . RDP_LIG_PLUGIN::$plugin_slug . '">'.esc_html__("Settings", RDP_LIG_PLUGIN::$plugin_slug).'</a>';
         array_unshift($links, $settings_link);
        }
        return $links;
     }

}//RDP_LIG_AdminMenu



/* EOF */
