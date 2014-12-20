# SGallery

* [Main Principles](#main-principles)
* [Installation](#installation)
* [Configuration](#configuration)

SGallery stands for SimpleGallery or SpeedeGallery or SuperfastGallery
or ... you get the idea.


```
curl -O http://get.hoborglabs.com/sgallery/sg.phar
php sg.phar install
php sg.phar update
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

create configuration file 'sgallery.properties' and specify source folder with your images, and target folder for
generating gallery assets.

```
source = '/full/path/to/photos/folder'
target = '/full/path/to/vhost/public'
```




## Folder Structure

After running SG you will find following folders in your target folder.
```
+ static
  + styles
  + scripts
  + images
  + json
+ albums
  + your-album-01
  + ...
+ index.html
```
