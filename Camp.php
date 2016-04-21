<?php

require_once dirname(__FILE__) . '/HTML5/Parser.php';

class Camp
{
    /**
     * The DOMDocument/HTML5 object
     * @var object
     */
    private $doc;
    
    /**
     * Original HTML of the content
     * @var string
     */
    public $html;
    
    /**
     * AMP version of the HTML content.
     * @var string
     */
    public $amp;
    
    /**
     * List of used amp component on the content.
     * @var array
     */
    public $components = array();
    
    /**
     * Download image to find image size
     * @var boolean
     */
    public $downloadImage = false;
    
    /**
     * local path to find the image for relative image src
     * @var string
     */
    public $localImagePath = '';
    
    /**
     * local hostname to download the image if the image not found on local path
     * for image with relative src attribute
     * @var string
     */
    public $localHost = '';
    
    /**
     * Default image width size if no way to find image size.
     * @var integer
     */
    public $defaultWidth = 300;
    
    /**
     * Default image height size if no way to find image size.
     * @var integer
     */
    public $defaultHeight = 200;
    
    /**
     * Constructor
     * @param string html The HTML content.
     * @param array options List of options.
     */
    function __construct($html=null, $options=null){
        if($html || $options)
            $this->convert($html, $options);
    }
    
    /**
     * Add new component to the list
     * @param string comp The component name.
     * @return $this
     */
    private function _addComponent($comp){
        if(!in_array($comp, $this->components))
            $this->components[] = $comp;
        return $this;
    }
    
    /**
     * Clean all invalid closing tag
     * @return string
     */
    private function _cleanHTMLClosingTag($text){
        $invalid_closing_tag = array(
            '</source>'
        );
        return str_replace($invalid_closing_tag, '', $text);
    }
    
    /**
     * Clean all prohibited tags
     * @return $this
     */
    private function _cleanProhibitedTag(){
        $tags = array(
            'script',   // The `application/ld+jxon` should be on head tag.
            'base',
            'frame',
            'frameset',
            'object',
            'param',
            'applet',
            'embed',
            'form',
            'input',    // except type is `button`.
            'textarea',
            'select',
            'option',
            'style',    // accepted only on head tag.
            'link',     // accepted if rel is `canonical`.
            'meta',     // only if with attribute `http-equiv`.
            'a',        // if href start with `javascript:` and target is not `_blank`.
//             'svg'       // TODO
        );
        
        foreach($tags as $tag){
            $els = $this->doc->getElementsByTagName($tag);
            
            if(!$els->length)
                continue;
            
            for($i=($els->length-1); $i>=0; $i--){
                $el = $els->item($i);
                
                if($tag == 'input'){
                    if($el->getAttribute('type') == 'button')
                        continue;
                }elseif($tag == 'link'){
                    if($el->getAttribute('rel') == 'canonical')
                        continue;
                }elseif($tag == 'meta'){
                    if(!$el->hasAttribute('http-equiv'))
                        continue;
                }elseif($tag == 'a'){
                    if(substr($el->getAttribute('href'), 0, 11) == 'javascript:'){
                        if(strtolower($el->getAttribute('target')) == '_blank')
                            continue;
                    }else{
                        continue;
                    }
                }
                
                $el->parentNode->removeChild($el);
            }
        }
        
        return $this;
    }
    
    /**
     * Clean prohibited attribute
     * @return $this
     */
    private function _cleanProhibitedAttribute(){
        // remove global prohibited attribute
        $prohibited_attrs = array(
            'align' => '*',
            'cellspacing' => '*',
            'start' => '*',
            'style' => '*',
            'summary' => array('table'),
            'width' => array('td')
        );
        
        $xpath = new DOMXPath($this->doc);
        
        foreach($prohibited_attrs as $attr => $tag){
            $els = $xpath->query("//*[@$attr]");
            if(!$els->length)
                continue;
            
            foreach($els as $el){
                $tag_name = $el->tagName;
                if($tag != '*' && !in_array($tag_name, $tag))
                    continue;
                
                if($el->hasAttribute($attr))
                    $el->removeAttribute($attr);
            }
        }
        
        return $this;
    }
    
    /**
     * Convert various ad to amp-ad 
     * @return $this
     */
    private function _convertAd(){
        // gads using ins
        $inses = $this->doc->getElementsByTagName('ins');
        if($inses->length){
            for($i=($inses->length-1); $i>=0; $i--){
                $ins = $inses->item(0);
                if($ins->getAttribute('class') != 'adsbygoogle')
                    continue;
                
                $attr = $this->_getAttribute($ins, array(
                    'data-ad-client' => true,
                    'data-ad-slot'   => true
                ));
                
                if(!$attr['data-ad-client'] || !$attr['data-ad-slot'])
                    continue;
                
                $amp_ad = $this->doc->createElement('amp-ad');
                $attr['type'] = 'adsense';
                $attr['height'] = 200;
                $attr['width'] = 300;
                
                $this->_setAttribute($amp_ad, $attr);
                $ins->parentNode->replaceChild($amp_ad, $ins);
                
                $this->_addComponent('amp-ad');
            }
        }
        
        return $this;
    }
    
    /**
     * Convert facebook 
     * @return $this
     */
    private function _convertFacebook(){
        $divs = $this->doc->getElementsByTagName('div');
        
        $fb_href = null;
        
        if($divs->length){
            for($i=($divs->length-1); $i>=0; $i--){
                $div = $divs->item($i);
                
                if($div->getAttribute('id') == 'fb-root'){
                    $div->parentNode->removeChild($div);
                    continue;
                }
                
                $class = $div->getAttribute('class');
                if($class == 'fb-post' || $class == 'fb-video'){
                    $fb_href = $div->getAttribute('data-href');
                    $is_video = preg_match('/videos/', $fb_href);
                    $method = '_makeAmpFacebookPost';
                    $attrs = array('width'=>486, 'height'=>657);
                    
                    if($is_video){
                        $method = '_makeAmpFacebookVideo';
                        $attrs = array('width'=>552, 'height'=>574);
                    }
                    
                    if(substr($fb_href, 0, 6) != 'https:')
                        $fb_href = 'https://www.facebook.com/' . ltrim($fb_href, '/');
                    
                    $amp_fb = $this->$method($fb_href, $attrs);
                    
                    $div->parentNode->replaceChild($amp_fb, $div);
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Convert iFrame 
     * @return $this
     */
    private function _convertIframe(){
        $iframes = $this->doc->getElementsByTagName('iframe');
        
        if(!$iframes->length)
            return $this;
        
        $regexps = array(
        
            '_makeAmpYoutube' => array(
                'width' => 560,
                'height'=> 314,
                
                'regexps' => array(
                    array(
                        'regex' => '/youtu\.be\/([\w\-.]+)/',
                        'index' => 1
                    ),
                    array(
                        'regex' => '/youtube\.com(.+)v=([^&]+)/',
                        'index' => 2
                    ),
                    array(
                        'regex' => '/youtube\.com\/embed\/([\w\-.]+)/i',
                        'index' => 1
                    )
                )
            ),
            
            '_makeAmpFacebookVideo' => array(
                'width' => 750,
                'height'=> 472,
                
                'regexps' => array(
                    array(
                        'regex' => '/https:\/\/www\.facebook\.com\/([^\/]+)\/videos\/([0-9]+)/i',
                        'index' => 0
                    )
                )
            ),
            
            '_makeAmpVine' => array(
                'width' => 600,
                'height'=> 600,
                
                'regexps' => array(
                    array(
                        'regex' => '/vine\.co\/v\/([^\/]+)/',
                        'index' => 1
                    )
                )
            )
        );
        
        for($i=($iframes->length-1); $i>=0; $i--){
            $iframe = $iframes->item($i);
            $attrs = $this->_getAttribute($iframe, array(
                'src' => true,
                'width' => true,
                'height' => true,
                'frameborder' => false
            ));
            
            $attrs['width'] = (int)$attrs['width'];
            $attrs['height']= (int)$attrs['height'];
            
            if(!$attrs['width'])
                $attrs['width'] = $this->defaultWidth;
            if(!$attrs['height'])
                $attrs['height'] = $this->defaultHeight;
            
            $src = $attrs['src'];
            
            $amp_el = null;
            
            // let find out if it's one of known componentes
            foreach($regexps as $method => $rule){
                foreach($rule['regexps'] as $re){
                    if(preg_match($re['regex'], $attrs['src'], $m)){
                        if(!$attrs['width'])
                            $attrs['width'] = $rule['width'];
                        if(!$attrs['height'])
                            $attrs['height'] = $rule['height'];
                        
                        $amp_el = $this->$method($m[$re['index']], $attrs);
                        break 2;
                    }
                }
            }
            
            if(!$amp_el){
                $this->_addComponent('amp-iframe');
                $attrs['sandbox'] = 'allow-scripts allow-same-origin';
                $attrs['layout'] = 'responsive';
                
                // set http to https
                if(substr($attrs['src'], 0, 5) !== 'https')
                    $attrs['src'] = preg_replace('!^http:!', 'https:', $attrs['src']);
                
                $amp_el = $this->doc->createElement('amp-iframe');
                $this->_setAttribute($amp_el, $attrs);
            }
            
            if($amp_el)
                $iframe->parentNode->replaceChild($amp_el, $iframe);
        }
        
        return $this;
    }
    
    /**
     * Convert img tag to amp-img/amp-anim
     * @return $this
     */
    private function _convertImg(){
        $imgs = $this->doc->getElementsByTagName('img');
        if(!$imgs->length)
            return $this;
        
        while($imgs->length){
            $img = $imgs->item(0);
            $attr= $this->_getAttribute($img, array(
                'src'    => true,
                'width'  => true,
                'height' => true,
                'srcset' => false,
                'alt'    => false
            ));
            
            $attr['width'] = (int)$attr['width'];
            $attr['height']= (int)$attr['height'];
            
            if(!$attr['width'] || !$attr['height']){
                $is_relative = substr($attr['src'], 0, 4) !== 'http';
                $download_it = true;
                
                // let see if it's using relative path
                if($is_relative){
                    if($this->localImagePath){
                        $img_local = chop($this->localImagePath, '/') . '/';
                        $img_local.= ltrim($attr['src'], '/');
                        
                        if(file_exists($img_local)){
                            list($attr['width'], $attr['height']) = getimagesize($img_local);
                            $download_it = false;
                        }
                    }
                }
                
                // look like we need to download it.
                if($download_it && $this->downloadImage){
                    $img_src = $attr['src'];
                    if($is_relative && $this->localHost)
                        $img_src = chop($this->localHost, '/') . '/' . ltrim($img_src, '/');
                    
                    // let download the image
                    if(filter_var($img_src, FILTER_VALIDATE_URL)){
                        $img_file = tempnam(sys_get_temp_dir(), "camp");
                    
                        $cu = curl_init($img_src);
                        curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($cu, CURLOPT_FOLLOWLOCATION, 1);
                        
                        $bin = curl_exec($cu);
                        curl_close($cu);
                        
                        $f = fopen($img_file, 'w');
                        fwrite($f, $bin);
                        fclose($f);
                        
                        list($attr['width'], $attr['height']) = getimagesize($img_file);
                        unlink($img_file);
                    }
                }
            }
            
            if(!$attr['width'])
                $attr['width'] = $this->defaultWidth;
            if(!$attr['height'])
                $attr['height']= $this->defaultHeight;
            
            // TODO findout how to use different layout type
            $attr['layout'] = 'responsive';
            
            $tag = preg_match('!\.gif$!', $attr['src']) ? 'amp-anim' : 'amp-img';
            $amp_img = $this->doc->createElement($tag);
            $this->_setAttribute($amp_img, $attr);
            
            if($tag == 'amp-anim')
                $this->_addComponent($tag);
            
            $img->parentNode->replaceChild($amp_img, $img);
        }
        
        return $this;
    }
    
    /**
     * Convert twitter embed
     * @return $this
     */
    private function _convertTwitter(){
        $blockquotes = $this->doc->getElementsByTagName('blockquote');
        if(!$blockquotes->length)
            return $this;
            
        for($i=($blockquotes->length-1); $i>=0; $i--){
            $twitter = $blockquotes->item($i);
            if($twitter->getAttribute('class') != 'twitter-tweet')
                continue;
            
            $anchor = $twitter->getElementsByTagName('a');
            if(!$anchor->length)
                continue;
            
            $anchor = $anchor->item(($anchor->length-1));
            
            $twitter_id = null;
            $regex = '!^https:\/\/twitter.com\/(\w+)\/status\/([0-9]+)!';
            if(preg_match($regex, $anchor->getAttribute('href'), $m))
                $twitter_id = $m[2];
            
            if(!$twitter_id)
                continue;
            
            $amp_twitter = $this->doc->createElement('amp-twitter');
            $attrs = array(
                'width' => 486,
                'height'=> 657,
                'layout'=> 'responsive',
                'data-tweetid' => $twitter_id,
                'data-cards' => 'hidden'
            );
            
            $this->_setAttribute($amp_twitter, $attrs);
            
            $twitter->parentNode->replaceChild($amp_twitter, $twitter);
            
            $this->_addComponent('amp-twitter');
        }
        
        return $this;
    }
    
    /**
     * Convert video tag
     * @return $this
     */
    private function _convertVideo(){
        $videos = $this->doc->getElementsByTagName('video');
        if(!$videos->length)
            return $this;
        
        for($i=($videos->length-1); $i>=0; $i--){
            $video = $videos->item($i);
            
            $attr= $this->_getAttribute($video, array(
                'src'      => false,
                'width'    => true,
                'height'   => true,
                'poster'   => false,
                'autoplay' => false,
                'controls' => false,
                'loop'     => false,
                'muted'    => false
            ));
            
            $attr['width'] = (int)$attr['width'];
            $attr['height']= (int)$attr['height'];
            
            // convert http(s):// to //
            if(array_key_exists('src', $attr))
                $attr['src'] = preg_replace('!^http(s?):!', '', $attr['src']);
            
            if(!$attr['width'])
                $attr['width'] = $this->defaultWidth;
            if(!$attr['height'])
                $attr['height']= $this->defaultHeight;
            
            $amp_video = $this->doc->createElement('amp-video');
            $this->_setAttribute($amp_video, $attr);
            
            $placeholder = [];
            if($video->hasChildNodes()){
                $video_child_count = $video->childNodes->length;

                for($j=($video_child_count-1); $j>=0; $j--){
                    $child = $video->childNodes->item($j);
                    if($child->nodeName == '#text'){
                        $text = trim($child->textContent);
                        if($text)
                            $placeholder[] = $text;
                    }elseif($child->nodeName == 'source'){
                        $amp_video->appendChild($child);
                        $child_src = $child->getAttribute('src');
                        $child_src = preg_replace('!^http(s?):!', '', $child_src);
                        $child->setAttribute('src', $child_src);
                    }
                }
            }
            
            // html5 fallback
            if($placeholder){
                $div_placeholder = $this->doc->createElement('div');
                $this->_setAttribute($div_placeholder, array('fallback'=>''));
                $div_placeholder->textContent = implode(PHP_EOL, $placeholder);
                $amp_video->appendChild($div_placeholder);
            }
            
            $video->parentNode->replaceChild($amp_video, $video);
        }
        
        return $this;
    }
    
    /**
     * Get element attribute
     * @param object node The node where the attr taken.
     * @param array attrs attr-required pair of attribute to get.
     * @return array name-value pair of the element attribute
     */
    private function _getAttribute($node, $attrs){
        $result = array();
        foreach($attrs as $attr => $must){
            $value = $node->getAttribute($attr);
            if($node->hasAttribute($attr) || $must)
                $result[$attr] = $value;
        }
        
        return $result;
    }
    
    /**
     * Make amp-facebook component
     * @param string id Facebook post URL
     * @param array attrs List of element attribute
     * @return object amp-facebook node
     */
    private function _makeAmpFacebookPost($id, $attrs){
        unset($attrs['src']);
        if(array_key_exists('frameborder', $attrs))
            unset($attrs['frameborder']);
        
        $attrs['layout'] = 'responsive';
        $attrs['data-href'] = $id;
        
        $amp_facebook = $this->doc->createElement('amp-facebook');
        $this->_setAttribute($amp_facebook, $attrs);
        
        $this->_addComponent('amp-facebook');
        
        return $amp_facebook;
    }
    
    /**
     * Make amp-facebook component
     * @param string id Facebook vidoe URL
     * @param array attrs List of element attribute
     * @return object amp-facebook node
     */
    private function _makeAmpFacebookVideo($id, $attrs){
        unset($attrs['src']);
        if(array_key_exists('frameborder', $attrs))
            unset($attrs['frameborder']);
        
        $attrs['layout'] = 'responsive';
        $attrs['data-embed-as'] = 'video';
        $attrs['data-href'] = $id;
        
        $amp_facebook = $this->doc->createElement('amp-facebook');
        $this->_setAttribute($amp_facebook, $attrs);
        
        $this->_addComponent('amp-facebook');
        
        return $amp_facebook;
    }
    
    /**
     * Make amp-youtube component
     * @param string id Youtube video id
     * @param array attrs List of element attribute
     * @return object amp-youtube node
     */
    private function _makeAmpYoutube($id, $attrs){
        unset($attrs['src']);
        if(array_key_exists('frameborder', $attrs))
            unset($attrs['frameborder']);
        
        $attrs['data-videoid'] = $id;
        $attrs['layout'] = 'responsive';
        
        $amp_youtube = $this->doc->createElement('amp-youtube');
        $this->_setAttribute($amp_youtube, $attrs);
        
        $this->_addComponent('amp-youtube');
        
        return $amp_youtube;
    }
    
    /**
     * Make amp-vine component
     * @param string id Vine video id
     * @param array attrs List of element attribute
     * @return object amp-vine node
     */
    private function _makeAmpVine($id, $attrs){
        unset($attrs['src']);
        if(array_key_exists('frameborder', $attrs))
            unset($attrs['frameborder']);
        
        $attrs['data-vineid'] = $id;
        
        $amp_vine = $this->doc->createElement('amp-vine');
        $this->_setAttribute($amp_vine, $attrs);
        
        $this->_addComponent('amp-vine');
        
        return $amp_vine;
    }
    
    /**
     * Set element attribute
     * @param object node The node where the element will be put
     * @param array attrs name-value pair of attribute to set.
     */
    private function _setAttribute(&$node, $attrs){
        foreach($attrs as $att => $value)
            $node->setAttribute($att, $value);
    }
    
    /**
     * Parse the HTML text.
     * @param string html The html content to convert.
     * @param array options List of convertion 
     * @return $this
     */
    public function convert($html=null, $options=null){
        if($options)
            $this->setOptions($options);
        if($html)
            $this->html = $html;
        
        $this->amp = null;
        $this->components = array();
        
        if(!$this->html)
            return $this;
            
        $html = '<!DOCTYPE html><html><body>' . $this->html . '</body></html>';
        $this->doc = HTML5_Parser::parse($html);
        
        $this
            ->_convertVideo()
            ->_convertImg()
            ->_convertAd()
            ->_convertTwitter()
            ->_convertFacebook()
            ->_convertIframe()
            ->_cleanProhibitedTag()
            ->_cleanProhibitedAttribute();
        
        $amp = $this->doc->saveHTML();
        $amp = $this->_cleanHTMLClosingTag($amp);
        preg_match('!^.+<body>(.+)</body>.+$!s', $amp, $m);
        $this->amp = $m[1];
        
        return $this;
    }
    
    /**
     * Set option(s)
     * @param string|array key The options key or list of option-value pair
     * @param mixed value The option value, only if $key is string
     * @return $this
     */
    public function setOptions($key, $value=null){
        if(!is_array($key))
            $key = array($key=>$value);
        
        foreach($key as $name => $value)
            $this->$name = $value;
    }
}