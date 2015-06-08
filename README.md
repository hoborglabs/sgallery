# SGallery

* [Main Principles](#main-principles)
* [Installation](#installation)
* [Configuration](#configuration)
* [Folder Structure](#folder-structure)

SGallery stands for SimpleGallery or SpeedeGallery or SuperfastGallery
or ... you get the idea.

```
curl -O http://get.hoborglabs.com/sgallery/sg.phar
chmod +x sg.phar
./sg.phar configure
./sg.phar install
./sg.phar update
```




## Main Principles

Whole gallery is generated up-front using your folder with photos. And by whole gallery I mean HTML, JSON for frontend
ajax calls, CSS, JS, and image thumbnails.  
You can use it to generate your gallery on your PC and upload it to your web server.

You need PHP 5.3 (or higher) to run it from command line.

After [installing](#installation) and configuring all you need to do is run single command

~~~~~
./sg.phar update
~~~~~

and that's it!




## Installation

Download Simple Gallery
```
curl -O http://get.hoborglabs.com/sgallery/sg.phar
chmod +x sg.phar
```




### Development

Clone repo

```
git clone git://github.com/hoborglabs/sgallery.git
cd sgallery
```

Install vendors
```
curl -s https://getcomposer.org/installer | php
php composer.phar update
```

If you want to update JS, you will have to run following command after putting your changes.

~~~~~
ant build.js
php src/sg.php install:assets
~~~~~

If you want to update CSS, you need to run the following command after changing CSS files

~~~~~
ant build.css
php src/sg.php install:assets
~~~~~




## Configuration

To configure or re-configure sgallery simply run `./sg.phar configure`. You can also manualy edit 'sg.properties' and
specify source folder with your images, and target folder for generating gallery assets and all other options.

### source

Source folder with your photos.
~~~ini
source = '/Users/wojtek/Pictures/'
~~~

### target

Target folder - usually public folder of your host
~~~ini
target = '/var/www/sgallery/public'
~~~

### skin

Skin name, being a folder name inside templates.
~~~ini
skin = hoborglabs
~~~

### language

Language.
~~~ini
language = en
~~~

### public files and folders mode

~~~ini
public.folderMode = 0755
public.fileMode = 0644
~~~

### thumbnails

~~~ini
; Quality for generated thumbnails. 100 no compression, 0 full
; compression.
thumbnails.quality = 75

; Size for generated thumbnails
thumbnails.size = 230

; Max width or height of your source Image. Depending on your PHP
; memory limit settings. For 128M keep it around 4000.
thumbnails.sourceMaxSize = 4000
~~~

### covers limits

~~~ini
covers.limit.2tile = 16
covers.limit.1tile = 8
~~~




## Folder Structure

After running SG you will find following folders in your target folder.
```
├── albums
|   ├── your-album-01
|   └── ...
├── index.html
└── static
    ├── json
    ├── scripts
    ├── styles
    └── thumbnails
```
