Therac - a collaborative debugger
-

Therac is a PHP (DBGp) debugger that hopefully does all the things you
expect from a standard debugger. However, especially tailored to perform
debugging with a friend. With the following core features.

* **State is synchronized across multiple browsers, so you can fix problems with a friend**
![alt text](http://i.imgur.com/xjd2Ei1.gif "synchronized across multiple browsers")

* **REPL to debug and write code on the fly**
![alt text](http://i.imgur.com/AiwhlhU.gif "REPL")

* **Easy variable display, updated when using the REPL and changing stack frames**
![alt text](http://i.imgur.com/x6e1HUm.png "REPL")

Requirements
--
Therac requires a HTTP server to serve the frontend and a PHP install with the [xdebug extension](http://xdebug.org/)

Building
--
Therac uses [Composer](https://getcomposer.org) and NPM for library management, it then uses browserify to concat the frontend.

To download the libraries from Composer run `composer update`, you are now ready to run the backend!

To download the libraries for NPM and build run `npm install && npm run build`

Running
--
First you must have the proper Xdebug config, in your `php.d` directory create a xdebug_therac.ini

    xdebug.remote_enable = 1
    xdebug.remote_host = 127.0.0.1
    xdebug.remote_port = 9089
    xdebug.remote_autostart = 1;
    xdebug.remote_connect_back = 0;
    Xdebug.remote_mode = req;

To run Therac execute `./bin/Therac.php` from the root of the project. Run `./bin/Therac.php --help` to see the available options.
At the very least you will need to specify a `--base-directory` this determines the contents of the file tree.

The following is an example nginx config for serving the frontend, and proxying WebSocket requests to Therac

    server {
      listen       80;
      root   PATH_TO_YOUR_VAGRANT_CHECKOUT/web/public_html;
      index  index.php index.html index.htm;

      location /websocket {
        proxy_pass http://localhost:4433;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
      }
    }
