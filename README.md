# SGallery

SGallery stands for SimpleGallery or SpeedeGallery or SuperfastGallery
or ... you get the idea.





## Main Principles

Whole gallery is generated up-front using your folder with photos. And by whole gallery I mean
HTML, JSON for frontend ajax calls, CSS, JS, and image thumbnails.

You need PHP to run it from command line.

After uploading (scp, ftp) images to your web server just run
~~~~~
php sg.php refresh
~~~~~
and that's it!

If for any reason you want to regenerate thumbnails or CSS only, simply run
~~~~~
php sg.php refresh:css
~~~~~

For more options check
~~~~~
php sg.php help
~~~~~





## Installation

~~~~~
git clone git://github.com/hoborglabs/sgallery.git
curl -s https://getcomposer.org/installer | php
php composer.php install
~~~~~

Building JS
~~~~~
node scripts/r.js -o scripts/hoborglabs/app.build.js
~~~~~

Building CSS
~~~~~
recess --compress styles/hoborglabs/less/main.less > dist/static/styles/hoborglabs/css/main.css
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
