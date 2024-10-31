<?php if ( ! defined('WP_CONTENT_DIR')) exit('No direct script access allowed'); ?>
<?php
/**
 * Description of rdpLIGReferencedItem
 *
 * @author Robert
 */
class RDP_LIG_ReferencedItem {
    static function fetch(){
        check_ajax_referer( 'rdp-lig-referenced-item-'.$_POST['key'], 'security' );
        $dataPass = array();
        $urlSchemes = array("http", "https");
        $urlPieces = parse_url($_POST['url']);
        $urlPathPieces = pathinfo($_POST['url']);

        if(empty($urlPieces['scheme']) || !in_array($urlPieces['scheme'], $urlSchemes) ){
            $dataPass['code'] = '406';
            $dataPass['message'] = 'Referenced item URL is not acceptable. It must be anvalid URL starting with http:// or https://.';
        }else{

            $html = rdp_file_get_html($_POST['url']);
            if(!$html){
                $dataPass['code'] = '400';
                $dataPass['message'] = 'Unable to retrieve data from the referenced URL.';
            }else{
                $mainContent = $html->find('main',0);
                if(!$mainContent)$mainContent = $html->find('div.main',0);
                if(!$mainContent)$mainContent = $html->find('div#main',0);
                if(!$mainContent)$mainContent = $html->find('.content',0);
                if(!$mainContent)$mainContent = $html->find('#content',0);
                if(!$mainContent)$mainContent = $html->find('.main-content',0);
                if(!$mainContent)$mainContent = $html->find('#main-content',0);
                if(!$mainContent)$mainContent = $html;


                $sTitle = self::_fetchTitle($html,$mainContent);
                $sSummary = self::_fetchSummary($html,$mainContent);
                $oImages = self::_fetchImages($mainContent,$urlPathPieces,$urlSchemes);
                $dataPass = array(
                    'code' => '200',
                    'html' => self::_renderReferencedItem($sTitle,$sSummary,$oImages,$urlPieces),
                    'images' => $oImages,
                    'title' => $sTitle,
                    'summary' => $sSummary,
                    'url' => $_POST['url']
                );
            }
            $html->clear();
            unset($html);
        }

        echo json_encode($dataPass);
        die();
    }//fetch

    private static function _renderReferencedItem($sTitle,$sSummary,$oImages,$oURLPieces){
        $sHTML = '<div id="share-preview-in">';
        $sHTML .= '<a class="share-close">Close</a>';

        if(count($oImages)){
            $sHTML .= '<div id="share-image" class="share-image">';
            $sHTML .= '<div class="sharing-carosel" id="control_gen_9">';
            $sHTML .= '<div class="sheen">';
            $sHTML .= '<img src="' . $oImages[0] . '" alt="' . $sTitle . '" border="0" width="160"/>';
            $sHTML .= '</div><!-- end .sheen -->';
            $sHTML .= '<p class="controls"' .(count($oImages) <= 1 ? '': ' style="display: block;"') . '>';
            $sHTML .= '<button class="previous"></button><!-- end .previous -->';
            $sHTML .= '<span class="min-max-count">';
            $sHTML .= '<span class="current">1</span> of <span class="total">' . count($oImages) . '</span>';
            $sHTML .= '</span><!-- end .min-max-count -->';
            $sHTML .= '<button class="next"></button><!-- end .next -->';
            $sHTML .= '</p><!-- end .controls -->';
            $sHTML .= '</div><!-- end #control_gen_9 -->';
            $sHTML .= '</div><!-- end #share-image -->';
        }//if(count($oImages))

        $sHTML .= '<div class="share-content" id="share-content">';
        $sHTML .= '<h4 class="share-view-title" id="share-view-title">' . $sTitle . '</h4>';
        $sHTML .= '<p id="share-view-meta" class="meta">' . $oURLPieces['host'] . '</p> &bull; ';
        $sHTML .= '<p class="share-summary">';
        $sHTML .= '<span class="share-view-summary" id="share-view-summary">' . $sSummary . '</span>';
        $sHTML .= '</p><!-- end .share-summary -->';
        $sHTML .= '<div class="toggle-img-content"></div>';
        $sHTML .= '</div><!-- end #share-content -->';

        $sHTML .= '</div><!-- end #share-preview-in -->';
        return $sHTML;
    }//_renderReferencedItem

    private static function _fetchImages($html,$urlPathPieces,$oURLSchemes){
        $images = array();
        $excludedImages = array();
        $ret = $html->find('meta[property=og:image]',0);
        if($ret && strlen($ret->content)){
            if(RDP_LIG_Utilities::isImage($ret->content))$images[] = $ret->content;
        }else{
            foreach($html->find('img') as $img){
                /* Make sure img src is http/https */
                $imgURLPieces = parse_url($img->src);
                if(!empty($imgURLPieces['scheme']) && !in_array($imgURLPieces['scheme'], $oURLSchemes) )continue;

                /* Begin: Skip images that may be nav bar images */
                $fSkip = false;
                $parent = $img->parent();
                for ($i = 1; $i < 5 ; $i++) {
                    if (!empty($parent->tag) && $parent->tag === 'li') {
                        $fSkip = true;
                        break;
                    }
                    $parent = $parent->parent();
                }
                if($fSkip)continue;
                /* End: Skip images that may be nav bar images */

                if(RDP_LIG_Utilities::isImage($imgURLPieces['path'])){
                    $picURL = '';
                    if(!empty($imgURLPieces['scheme']))$picURL = $img->src;

                    /* try to accommodate relative image paths */
                    if(strlen($picURL) == 0 && strpos($imgURLPieces['path'], '/') == 0){
                        $pos = strpos($urlPathPieces[dirname], '/', 8);
                        if($pos === FALSE)$pos = strlen ($urlPathPieces[dirname]);
                        $picURL = substr($urlPathPieces[dirname], 0, $pos ) . $imgURLPieces['path'];
                    }

                    if(strlen($picURL) != 0 && !in_array($picURL, $images) && !in_array($picURL, $excludedImages)){
                        $picURL = str_replace(' ','%20',$picURL);
                        $size = getimagesize($picURL);
                        /* Skip images that may be icons */
                        if($size && $size[0] > 69 && $size[1] > 69){
                            $images[] = $picURL;
                        }else{
                            $excludedImages[] = $picURL;
                        }
                    }
                }
            }//foreach($html->find('img') as $img)
        }//if(strlen($ret->content))

         return $images;
    }//_fetchImages


    private static function _fetchSummary($html,$mainContent) {
        $sSummary = null;
        $ret = $html->find('meta[property=og:description]',0);
        if($ret && strlen($ret->content))$sSummary = trim($ret->content);

        if(empty($sSummary)){
            $ret = $html->find('meta[name=description]',0);
            if($ret && strlen($ret->content))$sSummary = trim($ret->content);
        }

        if(empty($sSummary)){
            $ret = $mainContent->find('p',0);
            if($ret && strlen($ret->plaintext))$sSummary = trim($ret->plaintext);
        }

        if(empty($sSummary)){
            $ret = $mainContent->find('div.post_body',0);
            if($ret && strlen($ret->plaintext))$sSummary = trim($ret->plaintext);
        }

        if(empty($sSummary)){
            $ret = $mainContent->find('div.entry-content',0);
            if($ret && strlen($ret->plaintext))$sSummary = trim($ret->plaintext);
        }

        if(empty($sSummary))$sSummary = 'Unable to determine summary';
        if(strlen($sSummary)> 250)$sSummary = RDP_LIG_Utilities::truncateString(strip_tags($sSummary), 247,'...') ;
        return $sSummary;
    }//_fetchSummary

    private static function _fetchTitle($html,$mainContent){
        $sTitle = null;
        $ret = $html->find('meta[property=og:title]',0);
        if($ret && strlen($ret->content))$sTitle = trim($ret->content);

        if(empty($sTitle)){
            $ret = $mainContent->find('h1',0);
            if($ret && strlen($ret->plaintext))$sTitle = trim($ret->plaintext);
        }

        if(empty($sTitle)){
            $ret = $mainContent->find('h2',0);
            if($ret && strlen($ret->plaintext))$sTitle = trim($ret->plaintext);
        }

        if(empty($sTitle)){
            $ret = $mainContent->find('h3',0);
            if($ret && strlen($ret->plaintext))$sTitle = trim($ret->plaintext);
        }

         if(empty($sTitle)){
            $ret = $html->find('title',0);
            if($ret && strlen($ret->innertext))$sTitle = trim($ret->innertext);
        }

       if(empty($sTitle))$sTitle = 'Unable to determine title';
       if(strlen($sTitle) > 70)$sTitle = RDP_LIG_Utilities::truncateString(strip_tags($sTitle), 67,'...') ;
       return $sTitle;
    }//_fetchTitle

}//RDP_LIG_ReferencedItem


