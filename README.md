Therac - a collaborative debugger
-

Therac is a PHP (DBGp) debugger that hopefully does all the things you
expect from a standard debugger. However, especially tailored to perform
debugging with a friend. Some of my favorite features include.

* **State is synchronized across multiple browsers, so you can fix problems with a friend**
![alt text](http://i.imgur.com/SoLLD2A.gif "synchronized across multiple browsers")

* **REPL to debug and write code on the fly**
![alt text](http://i.imgur.com/AiwhlhU.gif "REPL")

* **Easy variable display, updated when using the REPL and changing stack frames**
![alt text](http://i.imgur.com/kEOYt65.png "Variable Display")

* **Catch exceptions in a background tab**
![alt text](http://i.imgur.com/CnvjfgH.png "Catch Exceptions")


Why Therac?
--
Therac isn't the most full featured PHP debugger, and probably never will be. However, it is designed with pair debugging and ease of use in mind.
The following design goals guide the project.

* **Do one thing, and do it well** -- Therac will only be a debugger, its feature set will stay small and easy to master
* **Easy to setup and deploy** -- Make it part of the default install in a developer environment, taking away the frustration of setting up and configuring a debugger.
* **Pair Debugging** -- Everything from scrolling to REPL input is shared between users, making debugging easier (especially for remote workers!) This also means you can close
the tab and when you come back your breakpoint will still be active.
* **Hackable** -- Most debuggers are written in C, or are integrated into existing IDEs. Therac is written in PHP/Javascript making it easy to improve and extend!

Requirements
--
Therac requires a HTTP server to serve the frontend, node.js to build the frontend, a PHP install with the [xdebug extension](http://xdebug.org/)

Building
--
Therac uses [Composer](https://getcomposer.org) and NPM for library management, it then uses browserify to concat the frontend.

To download the libraries from Composer run `composer update`, you are now ready to run the backend!

To download the libraries from NPM and build run `npm install && npm run build`

Running
--
Therac needs Xdebug it usually can be installed via pecl or your package manager, in your `php.d` directory create a xdebug_therac.ini

    zend_extension="xdebug.so"

    xdebug.remote_enable = 1
    xdebug.remote_autostart = 1;

To run Therac execute `./bin/Therac.php` from the root of the project. Run `./bin/Therac.php --help` to see the available options.
At the very least you will need to specify a `--base-directory` this determines the contents of the file tree.

The following is an example nginx config for serving the frontend, and proxying WebSocket requests to Therac

    server {
      listen       80;
      root   PATH_TO_YOUR_THERAC_CHECKOUT/web/public_html;
      index  index.html;

      location /websocket {
        proxy_pass http://localhost:4433;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
      }
    }
