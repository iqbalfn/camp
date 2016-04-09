Camp
====

Convert HTML main article to AMPHTML. Please note that this library is not meant
to convert entirely HTML document to AMP. Use the library to convert main article
of the page only, as in most case main article is the part where you generated
it by WYSIWYG ( ex: tinymce ). Another HTML part should rewrite to fullfill AMP
specifications.

Due to complicated processing in background, I suggest you to not to use the
library for every user request. Cache the result or save it to DB instead of
convert it everytime user ask for it.

**Warning**: This project still under heavy-development as the 
[ampproject](https://www.ampproject.org/) it self still in active development.

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

Dependencies
------------

1. [html5lib-php](https://github.com/html5lib/html5lib-php)  
Not to mention PHP 7.0.3, they still have no idea how to read HTML5 syntax.

Contribute
----------

This project is under MIT license, and hosted on github. You know what is that mean,
don't you?

TODO
----

1. All `amp-img`/`amp-anim` attribute `layout` always `responsive`. Need to
find out how to define different layout.
2. Remove unusable SVG syntax.
3. `amp-ad`. Support another ad provider, currently only support AdSense.
4. `amp-youtube`. Support for old youtube embed style ( no example to test )
5. Support another components  
amp-access-spec  
amp-audio  
amp-brightcove  
amp-carousel  
amp-dynamic-css-classes  
amp-fit-text  
amp-font  
amp-image-lightbox  
amp-instagram  
amp-install-serviceworker  
amp-lightbox  
amp-list  
amp-mustache  
amp-pinterest  
amp-pixel  
amp-slides  
amp-user-notification  
6. `amp-video` too set default `poster` attribute.
7. Remove `source` closing tag.

Bug?
----

You know, just report it.