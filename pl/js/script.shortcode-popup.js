var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_lig_shortcode_popup_onReady);

function rdp_lig_shortcode_popup_onReady(){
    $j('.wp-admin').on( "click", '#btnInsertLIGShortcode' , rdp_lig_insertShortcode ); 
    $j('.wp-admin').on( "change", '#ddLIGShortcode' , rdp_lig_ddLIGShortcode_onChange ); 

    
}//rdp_lig_shortcode_popup_onReady

function rdp_lig_insertShortcode(){
    var value = $j('#ddLIGShortcode').val();
    if(value == '*')return;
    var win = window.dialogArguments || opener || parent || top;
    var code = '';
    switch(value) {
        case 'login':
            win.send_to_editor('[rdp-ingroups-login]' );
            break;
        case 'Member Count':
            code = '[rdp-ingroups-member-count id=' + $j('#txtLIGID').val();
            if($j('#txtLIGLink').val() != '')code += ' link=' + $j('#txtLIGLink').val();
            if($j('#txtPrepend').val() != '')code += " prepend='" + $j('#txtPrepend').val() + "'"
            if($j('#txtAppend').val() != '')code += " append='" + $j('#txtAppend').val() + "'"
            if($j('#chkLIGNewWindow').prop( "checked")) code += ' new';
            code += ']';
            $j('#txtLIGID').val('');
            $j('#txtLIGLink').val('');
            $j('#txtPrepend').val('');
            $j('#txtAppend').val('');
            win.send_to_editor(code);
            break;
        case 'Discussion':
            if($j('#txtLIGLink').val().indexOf('linkedin.com/grp/') >= 0){
                var sUrl = $j('#txtLIGLink').val();
                
                if(sUrl.lastIndexOf("?") != -1){
                    sUrl = sUrl.substring(0, sUrl.lastIndexOf("?"));
                }

                var hostname = url('hostname', sUrl);
                var protocol = url('protocol', sUrl);
                var path = url('path', sUrl); 
                
                var pieces = sUrl.substr(sUrl.lastIndexOf('/')+1).split('-');
                
                code = "[rdp-ingroups-group id='"+pieces[0]+"' discussion_id='"+pieces[0]+ '-' +pieces[1]+"' discussion_url='"+protocol+"://"+hostname+path+"']";
                $j('#txtLIGLink').val('');
                $j('#ddLIGShortcode').val('*');
                win.send_to_editor(code);
            }
            break;
        default:
            code = '[rdp-ingroups-' + value.toLowerCase();
            if($j('#txtLIGID').val() != '') code += ' id=' + $j('#txtLIGID').val();
            if($j('#txtLIGDiscussionID').val() != '') code += " discussion_id='" + $j('#txtLIGID').val() + '-' + $j('#txtLIGDiscussionID').val() + "'";
            code += ']';
            $j('#txtLIGID').val('');
            $j('#txtLIGDiscussionID').val('');
            win.send_to_editor(code);
            break;
    }
}//rdp_lig_insertShortcode

function rdp_lig_ddLIGShortcode_onChange(){
    var value = $j('#ddLIGShortcode').val();
    $j('#txtLIGID').val('');
    $j('#txtLIGDiscussionID').val('');        
    $j('#txtLIGLink').val('');
    $j('#txtPrepend').val('');
    $j('#txtAppend').val('');
    $j('#txtLIGID-wrap').hide();
    $j('#txtLIGLink-wrap label').html('Link:');
    $j('#txtLIGLink-wrap').hide();
    $j('#ddLIGTemplate-wrap').hide();
    $j('#chkLIGNewWindow').prop( "checked", false );  
    $j('#chkLIGNewWindow-wrap').hide();    
    $j('#txtLIGDiscussionID-wrap').hide(); 
    $j('#txtPrepend-wrap').hide();
    $j('#txtAppend-wrap').hide();      
    
    switch(value) {
        case 'Discussion':
            $j('#txtLIGLink-wrap label').html('Discussion URL:*');
            $j('#txtLIGLink-wrap span').remove();
            $j('#txtLIGLink-wrap').show();
            break;        
        case 'Group':
            $j('#txtLIGID-wrap').show();
            $j('#txtLIGDiscussionID-wrap').show();    
            break;
        case 'Member Count':
            $j('#txtLIGLink-wrap').append('<span> (optional)</span>');
            $j('#txtLIGID-wrap').show();
            $j('#txtLIGLink-wrap').show();
            $j('#chkLIGNewWindow-wrap').show();
            $j('#txtPrepend-wrap').show();
            $j('#txtAppend-wrap').show();             
            break;
        default:
            break;
    }
    
}//rdp_lig_ddLIGShortcode_onChange