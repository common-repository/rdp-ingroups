var $j=jQuery.noConflict();
// Use jQuery via $j(...)

$j(document).ready(rdp_lig_global_onReady);
function rdp_lig_global_onReady(){
    rdp_lig_link_rewrite();
}//rdp_wcr_main_onReady


function rdp_lig_openPopupCenter(pageURL, title, w, h) {
    var left = (screen.width - w) / 2;
    var top = (screen.height - h) / 4;  // for 25% - divide by 4  |  for 33% - divide by 3
    var targetWin = window.open(pageURL, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
} 

function rdp_lig_get_source_element(e){
    if( !e ) e = window.event;
    var target;
    if(e.target||e.srcElement){
        target = e.target||e.srcElement;
    }else target = e;  
    return target;    
}//rdp_lig_get_source_element


function rdp_lig_link_rewrite(){
    if(!rdp_lig_global.my_groups_url)return;

    $j("a[href*='linkedin.com/groups/']").click(function(event){
        event.preventDefault();
        if(rdp_lig_global.my_groups_url == '')return true;
        var sHREF = $j(this).attr('href');
        var queryObject = jQuery.query.load(sHREF);        
        var pieces = sHREF.substr(sHREF.lastIndexOf('-')+1).split('.');
        var oID = pieces[0].split('/');
        queryObject.SET('rdpingroupsid',oID[0]); 
        if(typeof pieces[2] != 'undefined'){
            var oID2 = pieces[2].split('/');
            var postID = "g-"+oID[0]+ '-S-' +oID2[0];
            queryObject.SET('rdpingroupspostid',postID);
            queryObject.SET('rdpingroupsdiscussionurl',sHREF);
        }
        var params = queryObject.toString();
        window.location.href = rdp_lig_global.my_groups_url+params;
    });
}//rdp_lig_link_rewrite