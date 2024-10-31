<?php
header("Content-Type: application/xml; charset=ISO-8859-1");


class RDP_LIG_RSS {
    public $RSS = '';
    private $_id = '';
    private $_baseLink = '';
    
    function __construct(){
        $this->RSS = '<?xml version="1.0" encoding="ISO-8859-1" ?>';
        $this->_id = (isset($_REQUEST['id']))?strip_tags( $_REQUEST['id']  ):0;
        $this->_baseLink  = (isset($_REQUEST['link']))?$_REQUEST['link']:'';
        $fLIGShowManagerChoice = (isset($_REQUEST['mgr']))?$_REQUEST['mgr']:'off';
        if(empty($this->_id) || !is_numeric($this->_id)){
            $this->throwException('Invalid ID Supplied');
            return;
        }
        if(empty($this->_baseLink) || !filter_var($this->_baseLink, FILTER_VALIDATE_URL)){
            $this->throwException('Invalid Source Link Supplied');            
            return;
        }        
        $sLocation = '../bl/simple_html_dom.php';
        require_once $sLocation;
        $sLocation = '../bl/rdpLIGUtilities.php';
        require_once $sLocation;
        
        $url = "https://www.linkedin.com/grp/home?gid={$this->_id}";
        $html = rdp_file_get_html($url);

        if(!$html){
            $this->throwException('Bad Request');
            return;
        }else{
            $body = $html->find('div.layout-wrapper',0);
            if(!$body){
                $this->throwException('Bad Request');
                return;
            }
            
            foreach($body->find('script') as $script){
                $script->outertext = '';
            }
            
            
            $this->RSS .= '<rss xmlns:media="http://search.yahoo.com/mrss/" version="2.0"><channel>';
            $this->createChannelDetails($body);
            $this->createFeedItems($body,$fLIGShowManagerChoice);
            $this->RSS .= '</channel></rss>';            
        }

    }//__construct
    
    private function throwException($msg){
        $this->RSS .= "<error>{$msg}</error>";
    }
    
    private function createChannelDetails($html){
        
        $ret = $html->find('h1.entity-title a.group-name',0);
        $title = RDP_LIG_RSS::entitiesPlain($ret->innertext);
        $this->RSS .= "<title><![CDATA[{$title}]]></title>";
        $this->RSS .= "<link><![CDATA[{$this->_baseLink}]]></link>";
        $this->RSS .= "<description>Recent discussions from LinkedIn.</description>";
        $this->RSS .= "<ttl>12</ttl>";
        $ret = $html->find('.header img.image',0);
        $url = $ret->src;
        $this->RSS .= "<image>";
        $this->RSS .= "<url><![CDATA[{$url}]]></url>";        
        $this->RSS .= "<title><![CDATA[{$title}]]></title>";   
        $this->RSS .= "<link><![CDATA[{$this->_baseLink}]]></link>";        
        $this->RSS .= "</image>";  
    }//createChannelDetails
    
    private function createFeedItems($html,$fLIGShowManagerChoice){
        foreach($html->find('#content .disc-post') as $element){
            $sClass = $element->class;
            $aClasses = array();
            if($sClass){
                $aClasses = explode(' ', $sClass);
            }
            if($fLIGShowManagerChoice === 'off' && in_array('is-mgrs-choice', $aClasses) )continue;
            $this->RSS .= '<item>';
            
            $title = '';
            
            // discussion title
            $ret = $element->find('h3.post-title a',0);
            if($ret){
                $title = RDP_LIG_RSS::entitiesPlain($ret->plaintext);
                $href = $ret->href;
                $posA = strrpos($href, '/');
                $posB = strrpos($href, '?');
                $discussionID = substr($href, $posA + 1, $posB - ($posA + 1));
            } 
            
            $link = $this->_baseLink . "&rdpingroupspostid={$discussionID}";

            foreach ($element->find('a') as $anchor) {
                $anchor->href = $link;
            }
            
            $this->RSS .= "<title><![CDATA[{$title}]]></title>";             

            $description = '';
            
            $ret = $element->find('div.header-image',0);
            if($ret){
                $img = $ret->find('img.image',0);
                $img->width = '30';
                $img->height = '30';
                if($img)$ret->innertext = $img->outertext;
                $description .= RDP_LIG_RSS::entitiesPlain($ret->outertext);
            }
            
            $ret = $element->find('p.post-details',0);            
            if($ret){
                $description .= '<div>';
                $postText = RDP_LIG_RSS::entitiesPlain($ret->innertext);
                $description .=  RDP_LIG_Utilities::truncateString($postText, 100,' ...',false,true);
                $description .= '</div>';
            }

            $this->RSS .= "<description><![CDATA[{$description}]]></description>";
            $this->RSS .= "<link><![CDATA[{$link}]]></link>";
            $this->RSS .= "<guid isPermaLink='true'><![CDATA[{$link}]]></guid>";
            $ret = $element->find('.disc-article-preview',0);
            if($ret){
                $img = $ret->find('img',0);
                if($img)$imageUrl = $img->src;                
                $anchor = $ret->find('h4.title a',0);
                if($anchor){
                    $title = RDP_LIG_RSS::entitiesPlain($anchor->plaintext);
                }
                $this->RSS .= "<media:content url='{$imageUrl}' type='image/jpeg' expression='full'>";
                $this->RSS .= "<media:title type='plain'><![CDATA[{$title}]]></media:title>";
                $this->RSS .= "</media:content>";                
            }
            $this->RSS .= '</item>';
        }
  
    }//createFeedItems
    
    static function xmlEntities($string) { 
       return str_replace ( array ( '&', '"', "'", '<', '>', '�' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&apos;' ), $string ); 
    } 
    
    static function entitiesPlain($string){
        return str_replace ( array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;', '&quest;',  '&#39;' ), array ( '&', '"', "'", '<', '>', '?', "'" ), $string ); 
    }
}//RDP_LIG_RSS
$oRSS = new RDP_LIG_RSS();
echo $oRSS->RSS;
die();
/* EOF */
