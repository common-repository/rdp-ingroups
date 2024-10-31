<?php

class RDP_LIG_Shortcode_Popup {
    public static function addMediaButton($page = null, $target = null){
        global $pagenow;
        if ( !in_array( $pagenow, array( "post.php", "post-new.php" ) ))return;        
        $rdp_lig_button_src = plugins_url('/images/linkedin.ico', __FILE__);
        $output_link = '<a href="#TB_inline?width=400&inlineId=rdp-lig-shortcode-popup" class="thickbox button" title="inGroups+" id="rdp-lig-shortcode-button">';
        $output_link .= '<span class="wp-media-buttons-icon" style="background: url('. $rdp_lig_button_src.'); background-repeat: no-repeat; background-position: left bottom;"/></span>';
        $output_link .= '</a>';
        echo $output_link;
      
    }//addMediaButton
    
    public static function renderPopupForm(){
        global $pagenow;
        if ( !in_array( $pagenow, array( "post.php", "post-new.php" ) ))return;        
        echo '<div id="rdp-lig-shortcode-popup" style="display:none;">';
        echo '<h3>Insert inGroups+ Shortcode</h3>';
        echo '<select id="ddLIGShortcode">';
        echo '<option value="*">Choose shortcode</option>';
        echo '<option value="Group">Group Discussions</option>';
        echo '<option value="Discussion">Individual Discussion</option>';
        echo '<option value="login">Login Button</option>';        
        echo '<option value="Member Count">Member Count</option>';        
        echo '</select>';
        echo '<p id="txtLIGID-wrap" style="display:none;"><label for="txtLIGID">Group ID #:*</label> <input style="vertical-align: middle;width: 25%;padding: 2px;height: 28px;" type="text" id="txtLIGID"  value=""/></p>';
        echo '<p id="txtLIGDiscussionID-wrap" style="display:none;"><label for="txtLIGDiscussionID">Post ID #:</label> <input style="vertical-align: middle;padding: 2px;height: 28px;" type="text" id="txtLIGDiscussionID"  value=""/> (optional)</p>';
        echo '<p id="txtPrepend-wrap" style="display:none;"><label for="txtPrepend">Prepend Text:</label> <input style="vertical-align: middle;width: 25%;padding: 2px;height: 28px;" type="text" id="txtPrepend" value="" /> (optional)</p>';
        echo '<p id="txtAppend-wrap" style="display:none;"><label for="txtAppend">Append Text:</label> <input style="vertical-align: middle;width: 25%;padding: 2px;height: 28px;" type="text" id="txtAppend" value="" /> (optional)</p>';
        echo '<p id="txtLIGLink-wrap" style="display:none;"><label for="txtLIGLink">Link:</label> <input style="vertical-align: middle;width: 75%;padding: 2px;height: 28px;" type="text" id="txtLIGLink" value="" /></p>';
        echo '<p id="chkLIGNewWindow-wrap" style="display:none;"><input style="vertical-align: middle;padding: 2px;" type="checkbox" id="chkLIGNewWindow" value="" /> <label for="txtLIGLink">Open link in new window</label></p>';
        
        echo '<div>&nbsp;</div>';
        echo '<input type="button" value="Insert into Post/Page" id="btnInsertLIGShortcode" class="button">';
        echo '</div>';
        
        $script_src = plugins_url('/js/script.shortcode-popup.js', __FILE__);                
        wp_enqueue_script('rdp-lig-shortcode',$script_src, array('jquery'));
        
        $script_src = plugins_url('/js/url.min.js', __FILE__);
        wp_enqueue_script('jquery-url',$script_src, array('jquery','rdp-lig-shortcode'));
    }
    
    
}//RDP_LIG_Shortcode_Popup

/* EOF */
