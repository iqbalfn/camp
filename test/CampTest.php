<?php

require dirname(dirname(__FILE__)) . '/Camp.php';

class AmpImgTest extends PHPUnit_Framework_TestCase
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
                'lorem <script type="application/ld+json"></script> ipsum'
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
     */
    public function testProhibitedTags($html, $amp){
        $camp = new Camp($html);
        $this->assertEquals($amp, $camp->amp);
    }
}