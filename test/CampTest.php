<?php

use PHPUnit\Framework\TestCase;

require dirname(dirname(__FILE__)) . '/Camp.php';

class AmpImgTest extends TestCase
{
    
    /**************************************************************************
     * AMP-IMG | AMP-ANIM
     **************************************************************************/
     
    public function ampImgProvider(){
        return array(
        
            // amp-img
            'should convert `img` tag to `amp-img`' => array(
                'lorem <img src="lorem.png" width="200" height="100" alt="..."> ipsum',
                'lorem <amp-img src="lorem.png" width="200" height="100" alt="..." layout="responsive"></amp-img> ipsum'
            ),
                
            'should convert img to amp-img without alt' => array(
                'lorem <img src="lorem.jpg" width="250" height="150"> ipsum',
                'lorem <amp-img src="lorem.jpg" width="250" height="150" layout="responsive"></amp-img> ipsum'
            ),
                
            'should get image size from local file system' => array(
                'lorem <img src="/static/100x150.jpg"> ipsum',
                'lorem <amp-img src="/static/100x150.jpg" width="100" height="150" layout="responsive"></amp-img> ipsum',
                array('localImagePath'=>dirname(__FILE__))
            ),
                
            'should get image size from local host' => array(
                'lorem <img src="150x150.jpg"> ipsum',
                'lorem <amp-img src="150x150.jpg" width="150" height="150" layout="responsive"></amp-img> ipsum',
                array('downloadImage' => true, 'localHost' => 'https://placehold.it')
            ),
                
            'should get image size from different host' => array(
                'lorem <img src="https://placehold.it/200x150.jpg"> ipsum',
                'lorem <amp-img src="https://placehold.it/200x150.jpg" width="200" height="150" layout="responsive"></amp-img> ipsum',
                array('downloadImage' => true)
            ),
                
            'should use default image size' => array(
                'lorem <img src="/100x50.jpg"> ipsum',
                'lorem <amp-img src="/100x50.jpg" width="100" height="50" layout="responsive"></amp-img> ipsum',
                array('downloadImage' => false, 'defaultWidth'=>100, 'defaultHeight'=>50)
            ),
            
            // amp-anim
            'should convert img to amp-anim on gif file' => array(
                'lorem <img src="lorem.gif" width="350" height="150" alt=""> ipsum',
                'lorem <amp-anim src="lorem.gif" width="350" height="150" alt="" layout="responsive"></amp-anim> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampImgProvider
     * @group amp-image
     */
    public function testAmpImg($html, $amp, $options=array()){
        $camp = new Camp($html, $options);
        $this->assertEquals($amp, $camp->amp);
    }
    
    /**
     * @group amp-image
     */
    public function testAmpAnim(){
        $html = 'lorem <img src="lorem.gif" width="350" height="150" alt=""> ipsum';
        $camp = new Camp($html);
        
        $this->assertContains('amp-anim', $camp->components);
    }
    
    
    /**************************************************************************
     * AMP-AD
     **************************************************************************/
     
     public function ampAdProvider(){
        return array(
            array(
                'lorem <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script><!-- MRSNavResponsive --><ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-3216619137195954" data-ad-slot="9167553021" data-ad-format="auto"></ins><script>(adsbygoogle = window.adsbygoogle || []).push({});</script> ipsum',
                'lorem <!-- MRSNavResponsive --><amp-ad data-ad-client="ca-pub-3216619137195954" data-ad-slot="9167553021" type="adsense" height="200" width="300"></amp-ad> ipsum'
            )
        );
     }
     
     /**
      * @dataProvider ampAdProvider
      * @group amp-ad 
      */
    public function testAmpAd($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-FACEBOOK
     **************************************************************************/
     
     public function ampFacebookProvider(){
        return array(
            array(
                'lorem <div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));</script><div class="fb-post" data-href="https://www.facebook.com/zuck/posts/10102593740125791" data-width="500"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/zuck/posts/10102593740125791"><p>February 4 is Facebook&#x2019;s 12th birthday!Our anniversary has a lot of meaning to me as an opportunity to reflect on how...</p>Posted by <a href="https://www.facebook.com/zuck">Mark Zuckerberg</a> on&nbsp;<a href="https://www.facebook.com/zuck/posts/10102593740125791">Tuesday, January 12, 2016</a></blockquote></div></div> ipsum',
                'lorem <amp-facebook width="500" height="657" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook> ipsum'
            ),
            array(
                'lorem <div id="fb-root"></div><script>(function(d, s, id) {  var js, fjs = d.getElementsByTagName(s)[0];  if (d.getElementById(id)) return;  js = d.createElement(s); js.id = id;  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";  fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));</script><div class="fb-post" data-href="https://www.facebook.com/zuck/videos/10102509264909801/" data-width="500"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/zuck/videos/10102509264909801/"><p>I want to share a few more thoughts on the Chan Zuckerberg Initiative before I just start posting photos of me and Max...</p>Posted by <a href="https://www.facebook.com/zuck">Mark Zuckerberg</a> on&nbsp;<a href="https://www.facebook.com/zuck/videos/10102509264909801/">Friday, December 4, 2015</a></blockquote></div></div> ipsum',
                'lorem <amp-facebook width="500" height="657" layout="responsive" data-embed-as="video" data-href="https://www.facebook.com/zuck/videos/10102509264909801/"></amp-facebook> ipsum'
            ),
            array(
                'lorem <iframe width="476" height="476" allowfullscreen="allowfullscreen" src="https://www.facebook.com/plugins/video.php?href=https://www.facebook.com/FacebookIndonesia/videos/1078475638956142/&show_text=0&width=476"></iframe> ipsum',
                'lorem <amp-facebook width="476" height="476" layout="responsive" data-embed-as="video" data-href="https://www.facebook.com/FacebookIndonesia/videos/1078475638956142"></amp-facebook> ipsum'
            ),
            array(
                'lorem <iframe src="https://www.facebook.com/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2FFacebookIndonesia%2Fvideos%2F1078475638956142%2F&show_text=0&width=476" width="476" height="476" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allowFullScreen="true"></iframe> ipsum',
                'lorem <amp-facebook width="476" height="476" layout="responsive" data-embed-as="video" data-href="https://www.facebook.com/FacebookIndonesia/videos/1078475638956142/"></amp-facebook> ipsum'
            ),
            array(
                'lorem <iframe src="https://www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2Fzuck%2Fposts%2F10102593740125791&width=500" width="500" height="283" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true"></iframe> ipsum',
                'lorem <amp-facebook width="500" height="283" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook> ipsum'
            ),
            array(
                'lorem <div class="fb-video" data-allowfullscreen="1" data-href="/MiaChibbie/videos/vb.100003034897952/835741256537030/?type=3">&nbsp;</div> ipsum',
                'lorem <amp-facebook width="486" height="657" layout="responsive" data-embed-as="video" data-href="https://www.facebook.com/MiaChibbie/videos/vb.100003034897952/835741256537030/?type=3"></amp-facebook> ipsum'
            ),
            array(
                'lorem <iframe src="//www.facebook.com/plugins/post.php?href=https%3A%2F%2Fwww.facebook.com%2Fnikkigandangreynapalma%2Fposts%2F1709386189095516&amp;width=500" width="500" height="793"></iframe> ipsum',
                'lorem <amp-facebook width="500" height="793" layout="responsive" data-href="https://www.facebook.com/nikkigandangreynapalma/posts/1709386189095516"></amp-facebook> ipsum'
            )
        );
     }
     
     /**
      * @dataProvider ampFacebookProvider
      * @group amp-facebook
      */
    public function testAmpFacebook($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-IFRAME
     **************************************************************************/
     
     public function ampIFrameProvider(){
        return array(
            'should convert iframe to amp-iframe' => array(
                'lorem <iframe src="https://foo.com/iframe" height="300" width="300"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="300" sandbox="allow-scripts allow-same-origin" layout="responsive"></amp-iframe> ipsum'
            ),
            'should convert iframe with attribute frameborder to amp-iframe with frameborder' => array(
                'lorem <iframe src="https://foo.com/iframe" height="300" width="300" frameborder="0"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="300" frameborder="0" sandbox="allow-scripts allow-same-origin" layout="responsive"></amp-iframe> ipsum'
            ),
            'should set src http to https' => array(
                'lorem <iframe src="http://foo.com/iframe" height="300" width="300"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="300" sandbox="allow-scripts allow-same-origin" layout="responsive"></amp-iframe> ipsum'
            ),
            'should set dynamic protocol to https' => array(
                'lorem <iframe src="//foo.com/iframe" height="300" width="300"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="300" sandbox="allow-scripts allow-same-origin" layout="responsive"></amp-iframe> ipsum'
            ),
            'should fill iframe size' => array(
                'lorem <iframe src="https://foo.com/iframe"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="200" sandbox="allow-scripts allow-same-origin" layout="responsive"></amp-iframe> ipsum'
            ),
            'should add image placeholder' => array(
                'lorem <iframe src="https://foo.com/iframe"></iframe> ipsum',
                'lorem <amp-iframe src="https://foo.com/iframe" width="300" height="200" sandbox="allow-scripts allow-same-origin" layout="responsive"><amp-img layout="fill" src="/images/placeholder.png" placeholder="placeholder"></amp-img></amp-iframe> ipsum',
                ['iframePlaceholder' => '/images/placeholder.png']
            )
        );
     }
     
     /**
      * @dataProvider ampIFrameProvider
      * @group amp-iframe
      */
    public function testAmpIFrame($html, $amp, $opts=null){
        $camp = new Camp($html, $opts);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-INSTAGRAM
     **************************************************************************/
     
    public function ampInstagramProvider(){
        return array(
            'should convert instagram iframe embed to amp-instagram' => array(
                'lorem <iframe src="//www.instagram.com/p/Bah_gHPDaK_/embed" width="520" height="616"></iframe> ipsum',
                'lorem <amp-instagram width="520" height="616" data-shortcode="Bah_gHPDaK_" data-captioned="data-captioned" layout="responsive"></amp-instagram> ipsum'
            ),
            'should convert minimal istagram embed to amp-instagram' => array(
                'lorem <blockquote class="instagram-media" data-instgrm-captioned="" data-instgrm-version="7"><a href="https://www.instagram.com/p/Bah_gHPDaK_" target="_blank"></a></blockquote> ipsum',
                'lorem <amp-instagram width="320" height="320" data-shortcode="Bah_gHPDaK_" data-captioned="data-captioned" layout="responsive"></amp-instagram> ipsum'
            ),
            'should convert original instagram embed to amp-instagram' => array(
                'lorem '.file_get_contents(__DIR__.'/static/instagram-default.txt').' ipsum',
                'lorem <amp-instagram width="320" height="320" data-shortcode="Bah_gHPDaK_" data-captioned="data-captioned" layout="responsive"></amp-instagram> ipsum'
            ),
            'should convert without caption instagram embed to amp-instagram' => array(
                'lorem '.file_get_contents(__DIR__.'/static/instagram-without-caption.txt').' ipsum',
                'lorem <amp-instagram width="320" height="320" data-shortcode="Bah_gHPDaK_" layout="responsive"></amp-instagram> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampInstagramProvider
     * @group amp-instagram
     */
    public function testAmpInstagram($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-TWITTER
     **************************************************************************/
    
    public function ampTwitterProvider(){
        return array(
            array(
                'lorem <blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">32. <a href="https://twitter.com/officialR5">@officialR5</a> - Dark Side<a href="https://twitter.com/hashtag/FLIGHT40?src=hash">#FLIGHT40</a><a href="https://twitter.com/hashtag/CumaDiArdan?src=hash">#CumaDiArdan</a> <a href="https://t.co/z7BrNJllHl">pic.twitter.com/z7BrNJllHl</a></p>&mdash; 105.9 FM ARDAN Radio (@ardanradio) <a href="https://twitter.com/ardanradio/status/698353355203092480">February 13, 2016</a></blockquote><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script> ipsum',
                'lorem <amp-twitter width="486" height="657" layout="responsive" data-tweetid="698353355203092480" data-cards="hidden"></amp-twitter> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampTwitterProvider
     * @group amp-twitter
     */
    public function testAmpTwitter($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-YOUTUBE
     **************************************************************************/
    
    public function ampYoutubeProvider(){
        return array(
            array(
                'lorem <iframe width="560" height="315" src="https://www.youtube.com/embed/VKBSCAXkUuY" frameborder="0" allowfullscreen></iframe> ipsum',
                'lorem <amp-youtube width="560" height="315" data-videoid="VKBSCAXkUuY" layout="responsive"></amp-youtube> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampYoutubeProvider
     * @group amp-youtube
     */
    public function testAmpYoutube($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-VINE
     **************************************************************************/
    
    public function ampVineProvider(){
        return array(
            array(
                'lorem <iframe src="https://vine.co/v/iw7VxOJzut0/embed/simple" width="600" height="600" frameborder="0"></iframe><script src="https://platform.vine.co/static/scripts/embed.js"></script> ipsum',
                'lorem <amp-vine width="600" height="600" data-vineid="iw7VxOJzut0"></amp-vine> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampVineProvider
     * @group amp-vine
     */
    public function testAmpVine($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * AMP-VIDEO
     **************************************************************************/
    
    public function ampVideoProvider(){
        return array(
            'should convert video tag to amp-video' => array(
                'lorem <video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg" controls></video> ipsum',
                'lorem <amp-video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg" controls="" layout="responsive"></amp-video> ipsum'
            ),
            'should convert video tag to amp-video include placeholder' => array(
                'lorem <video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg">Your browser doesn\'t support HTML5 video</video> ipsum',
                'lorem <amp-video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg" layout="responsive"><div fallback="">Your browser doesn\'t support HTML5 video</div></amp-video> ipsum'
            ),
            'should convert video tag to amp-video and ignore space child' => array(
                'lorem <video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg"> </video> ipsum',
                'lorem <amp-video src="myvideo.mp4" width="400" height="300" poster="myvideo-poster.jpg" layout="responsive"></amp-video> ipsum'
            ),
            'should convert video tag to amp-video include all sub-source' => array(
                'lorem <video src="myvideo.mp4" width="400" height="300" controls><source src="movie.mp4" type="video/mp4">Your browser doesn\'t support HTML5 video</video> ipsum',
                'lorem <amp-video src="myvideo.mp4" width="400" height="300" controls="" layout="responsive"><source src="movie.mp4" type="video/mp4"><div fallback="">Your browser doesn\'t support HTML5 video</div></amp-video> ipsum'
            ),
            'should convert video tag to amp-video include all sub-sources' => array(
                'lorem <video src="myvideo.mp4" width="400" height="300"><source src="movie.mp4" type="video/mp4"><source src="movie.ogg" type="video/ogg">Your browser doesn\'t support HTML5 video</video> ipsum',
                'lorem <amp-video src="myvideo.mp4" width="400" height="300" layout="responsive"><source src="movie.ogg" type="video/ogg"><source src="movie.mp4" type="video/mp4"><div fallback="">Your browser doesn\'t support HTML5 video</div></amp-video> ipsum'
            ),
            'should convert video tag src attribute to relative protocol' => array(
                'lorem <video src="http://www.google.com/myvideo.mp4" width="400" height="300"><source src="http://www.google.com/movie.mp4" type="video/mp4">Your browser doesn\'t support HTML5 video</video> ipsum',
                'lorem <amp-video src="//www.google.com/myvideo.mp4" width="400" height="300" layout="responsive"><source src="//www.google.com/movie.mp4" type="video/mp4"><div fallback="">Your browser doesn\'t support HTML5 video</div></amp-video> ipsum'
            )
        );
    }
    
    /**
     * @dataProvider ampVideoProvider
     * @group amp-video
     */
    public function testAmpVideo($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * PROHIBITED TAG
     **************************************************************************/
     
    public function prohibitedProvider(){
        return array(
            'should remove script attribute' => array(
                'lorem <script>alert("a");</script> ipsum',
                'lorem  ipsum'
            ),
            
            'should leave script with type application/ld+json' => array(
                'lorem <script type="application/ld+json"></script> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove base tag' => array(
                'lorem <base href="http://www.w3schools.com/images/" target="_blank"> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove frame tag' => array(
                'lorem <frame src="frame_a.htm"> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove frameset tag' => array(
                'lorem <frameset cols="100%"><frame src="frame_a.htm"></frameset> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove object tag' => array(
                'lorem <object width="400" height="400" data="helloworld.swf"></object> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove param tag' => array(
                'lorem <param name="autoplay" value="true"> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove applet tag' => array(
                'lorem <applet code="Bubbles.class" width="350" height="350">Java applet that draws animated bubbles.</applet> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove embed tag' => array(
                'lorem <embed src="helloworld.swf"> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove form tag' => array(
                'lorem <form method="post"></form> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove input tag' => array(
                'lorem <input type="text"> ipsum',
                'lorem  ipsum'
            ),
            
            'should leave input type button' => array(
                'lorem <input type="button"> ipsum',
                'lorem <input type="button"> ipsum'
            ),
            
            'should remove input tag but leave input type button tag' => array(
                'lorem <input type="button"><input type="text"> ipsum',
                'lorem <input type="button"> ipsum'
            ),
            
            'should remove textarea tag' => array(
                'lorem <textarea></textarea> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove select tag' => array(
                'lorem <select><option value="a"></option></select> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove option tag' => array(
                'lorem <option value="a"></option> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove style tag' => array(
                'lorem <style>.body{background-color:white}</style> ipsum',
                'lorem  ipsum'
            ),
            
            'should remove link tag' => array(
                'lorem <link rel="stylesheet" href="css.css"> ipsum',
                'lorem  ipsum'
            ),
            
            'should leave link tag with rel canonical' => array(
                'lorem <link rel="canonical" href="blog.web"> ipsum',
                'lorem <link rel="canonical" href="blog.web"> ipsum'
            ),
            
            'should leave meta tag' => array(
                'lorem <meta name="lorem" content="ipsum"> ipsum',
                'lorem <meta name="lorem" content="ipsum"> ipsum'
            ),
            
            'should remove meta tag with http-equiv tag' => array(
                'lorem <meta http-equiv="X-UA-Compatible" content="IE=edge"> ipsum',
                'lorem  ipsum'
            ),
            
            'should leave a tag' => array(
                'lorem <a href="lorem">click</a> ipsum',
                'lorem <a href="lorem">click</a> ipsum'
            ),
            
            'should remove a tag with href start with javascript:' => array(
                'lorem <a href="javascript:alert(\'a\')">click</a> ipsum',
                'lorem  ipsum'
            ),
            
            'should leave a tag with href javascript and target _blank' => array(
                'lorem <a href="javascript:alert(\'a\')" target="_blank">click</a> ipsum',
                'lorem <a href="javascript:alert(\'a\')" target="_blank">click</a> ipsum'
            )
        );
    }
     
    /**
     * @dataProvider prohibitedProvider
     * @group prohibited-tag
     * @group prohibited
     */
    public function testProhibitedTags($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
    
    
    /**************************************************************************
     * PROHIBITED ATTRIBUTE
     **************************************************************************/
     
     public function prohibitedAttrProvider(){
        return array(
            array(
                'lorem <ol start="2"></ol> ipsum',
                'lorem <ol></ol> ipsum'
            ),
            array(
                'lorem <p style="text-align:center"></p> ipsum',
                'lorem <p></p> ipsum'
            ),
            array(
                'lorem <table cellspacing="5"></table> ipsum',
                'lorem <table></table> ipsum'
            ),
            array(
                'lorem <table summary="5"></table> ipsum',
                'lorem <table></table> ipsum'
            ),
            array(
                'lorem <table><tbody><tr><td width="100"></td><td></td></tr></tbody></table> ipsum',
                'lorem <table><tbody><tr><td></td><td></td></tr></tbody></table> ipsum'
            )
        );
     }
     
    /**
     * @dataProvider prohibitedAttrProvider
     * @group prohibited-attr
     * @group prohibited
     */
    public function testProhibitedAttrs($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
}