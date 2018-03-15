Camp
====

Convert HTML main article to AMPHTML. Please note that this library is not meant
to convert entirely HTML document to AMP. Use the library to convert main article
of the page only, as in most case main article is the part where we generated
it by WYSIWYG ( ex: tinymce ). Another HTML part should rewrite to fullfill AMP
specifications.

Due to complicated processing in background, I suggest to not to use the library
for every user request. Cache the result or save it to DB instead of convert it
everytime user ask for it.

Usage
-----

```php
<?php

require_once '/path/to/Camp.php';

$html = '<div id="main-content">...</div>';
$options = array(...);

$camp = new Camp($html, $options);

$amp   = $camp->amp;
$comps = $camp->components;

foreach($comps as $comp)
    echo '<script async custom-element="' . $comp . '" src="https://cdn.ampproject.org/v0/' . $comp . '-0.1.js"></script>';
```

Methods
-------

### setOptions ( string|array `$key`, mixed `$value` )

Set the options value

```php
<?php

// #1
$options = array(
    'defaultWidth' => 600,
    'defaultHeight'=> 450
);

$camp->setOptions($options);

// #2
$camp->setOptions('localHost', 'http://localhost');
```

### convert ( string `$html`, array `$options` )

Start convert the content.

```php
<?php

// #1

$html = '<div id="main-content">...</div>';
$opts = array(...);

$camp = new Camp($html, $opts);

$amp  = $camp->amp;
$comps= $camp->components;

// #2

$camp = new Camp;

$camp->html = '<div id="main-content">...</div>';
$camp->setOptions(...);

$camp->convert();

$amp   = $camp->amp;
$comps = $camp->comps;
```

Properties
----------

List of `Camp` properties.

### amp *string*

AMP version of the HTML content.

### html *string*

Original HTML of the content.

Options
-------

List of options that `Camp` understand and use during conversion.

### downloadImage *boolean*

Download the image if attribute `width` or `height` not found on the `img` tag.
Default `false`.

### localImagePath *string*

Find the image on local system in case the `src` attribute of the image is relative
url. This option value will be prefixed to the image `src` attribute as absolute
location of the image. Default ''.

```php

$camp = new Camp;
$camp->html = '<img src="/relative/path/to/image.jpg">';
$camp->setOptions('localImagePath', '/var/www/html');

// the image size taken from file "/var/www/html/relative/path/to/image.jpg"
```

### localHost *string*

Download the image from this host in case the image not found on local system
by option `localImagePath`. Used only if the attribute `src` of the image is
relative url. Default ''.

```php

$camp = new Camp;
$camp->html = '<img src="/relative/path/to/image.jpg">';
$camp->setOptions('localImagePath', '/var/www/html');
$camp->setOptions('localHost', 'http://example.com/');

// the image downloaded from  "http://example.com/relative/path/to/image.jpg"
// if the image not found on "/var/www/html/relative/path/to/image.jpg"
```

### defaultWidth *integer*

If no way to find image size, use this size instead. Default 300.

### defaultHeight *integer*

If no way to find image size, use this size instead. Default 200.

### iframePlaceholder *string*

Default image to use as `amp-iframe` image placeholder. The image is an `amp-img`
with attribute `layout=fill`.

### videoPoster *string*

Default image to use as default video poster if no poster found on the video tag.

Dependencies
------------

1. [html5lib-php](https://github.com/html5lib/html5lib-php)  
Not to mention PHP 7.0.3, they still have no idea how to read HTML5 syntax.

Contribute
----------

This project is under MIT license, and hosted on github. There's always new tag
that may visible on article content, I'll need help to add more parser/converter
for each element. Please see below list of element that need to convert.

TODO
----

1. Remove unusable SVG syntax.
1. `amp-ad`. Support another ad provider, currently only support AdSense.
1. Support another components  
    1. amp-3q-player  
    1. amp-apester-media  
    1. amp-audio  
    1. amp-brid-player  
    1. amp-brightcove  
    1. amp-carousel  
    1. amp-dailymotion  
    1. amp-gist  
    1. amp-google-vrview-image  
    1. amp-gfycat  
    1. amp-hulu  
    1. amp-ima-video  
    1. amp-image-lightbox  
    1. amp-imgur  
    1. amp-izlesene  
    1. amp-jwplayer  
    1. amp-kaltura-player  
    1. amp-lightbox  
    1. amp-list  
    1. amp-mustache  
    1. amp-nexxtv-player  
    1. amp-o2-player  
    1. amp-ooyala-player  
    1. amp-pinterest  
    1. amp-playbuzz  
    1. amp-reach-player  
    1. amp-reddit  
    1. amp-slides  
    1. amp-soundcloud  
    1. amp-springboard-player  
    1. amp-vimeo  
    1. amp-viz-vega  
    1. amp-vk  

Bug?
----

It's oke to create a ticket.