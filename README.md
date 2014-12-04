# SGallery

SGallery stands for SimpleGallery or SpeedeGallery or SuperfastGallery
or ... you get the idea.





## Main Principles

Whole gallery is generated up-front using your folder with photos. And by whole gallery I mean HTML, JSON for frontend
ajax calls, CSS, JS, and image thumbnails.  
You can use it to generate your gallery on your PC and upload it to your web server.

You need PHP 5.3 (or higher) to run it from command line.

After [installing](#installation) and configuring all you need to do is run single command
~~~~~
php src/sg.php update
~~~~~
and that's it!





## Installation

First clone repo
~~~~~
git clone git://github.com/hoborglabs/sgallery.git
cd sgallery
~~~~~

install vendors by downloading from get.hoborglabs.com
~~~~~
curl http://get.hoborglabs.com/sgallery/vendors.tar -O
tar -xf vendors.tar
~~~~~

or use composer
~~~~~
curl -s https://getcomposer.org/installer | php
php composer.phar update
~~~~~

And now install sgallery
~~~~~
php src/sg.php install
~~~~~

You can always change sgallery properties
~~~~~
vim conf/properties.ini
~~~~~





## Development

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





## Folder Structure

After running SG you will find following folders in your target folder.
~~~~~
+ static
  + styles
  + scripts
  + images
  + json
+ albums
  + your-album-01
  + ...
+ index.html
~~~~~
