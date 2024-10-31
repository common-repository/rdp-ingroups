var $j=jQuery.noConflict();
// Use jQuery via $j(...)
var rdp_lig_currentGroupId = 0;

$j(document).ready(rdp_lig_login_onReady);

function rdp_lig_login_onReady(){
    var nWidth = $j('#rdp-lig-head').width()*.65;
    $j('#rdp-lig-top-bar .left-entity').width(nWidth);
    $j('#rdp-lig-top-bar').show(300);   
    rdp_lig_clean_links();
    $j('body').on( "click", '.btnLGILogin' , rdp_lig_login_showPopup );          
}

function rdp_lig_clean_links(){
    $j('#rdp-lig-main .discussion-item-list a').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');    
    $j('#rdp-lig-main .top-bar .image-wrapper').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');
    $j('#rdp-lig-main .top-bar .parent-group a').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');
    $j('#rdp-lig-main .referenced-item a').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');    
    $j('#rdp-lig-main .entity a').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');
    $j('#rdp-lig-main a.poster').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');
    $j('#rdp-lig-main a.discussion-title').addClass( "btnLGILogin" ).removeAttr('href').removeAttr('target');
    $j('#rdp-lig-main .media-block a').addClass( "rdp-lig-pre-login-group" );  
    $j('#rdp-lig-main .media-block a').each(function( index ) {
        var str = $j(this).attr('href');
        parsestring1=str.match(/anetid=(\d+)/);
        $j(this).attr('id', parsestring1[1]);
        $j('img',$j(this)).attr('id', parsestring1[1]);
        $j(this).removeAttr('href'); 
    });
}

function rdp_lig_login_showPopup(e){
    if(e){
        var target = rdp_lig_get_source_element(e);
        var postID = (typeof $j(target).attr('postid') == 'undefined')? jQuery.query.get('rdpingroupspostid') : $j(target).attr('postid'); 
    }
    var sURL = rdp_lig_login.loginurl;
    var groupID = (rdp_lig_currentGroupId != 0)? rdp_lig_currentGroupId : $j('.defaultGroupID').attr('id') ;
    ord=Math.random()*10000000000000000;
    var queryObject = jQuery.query.load(rdp_lig_login.loginurl);
    queryObject.SET('rdplig',ord);
    queryObject.SET('rdpingroupsaction','login');

    if(groupID && typeof groupID != 'undefined')queryObject.SET("rdpingroupsid", groupID);
    if(typeof postID != 'undefined' && postID){
        queryObject.SET('rdpingroupspostid',postID);
    }else{
        oDiscussionID = $j('#txtLastDiscussionID');
        if(oDiscussionID.length)queryObject.SET('rdpingroupspostid',oDiscussionID.val());       
    }
    var params = queryObject.toString();
    sURL += params;
    rdp_lig_openPopupCenter(sURL, '', 400, 600);
}



