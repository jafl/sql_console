Bare-bones SQL console that runs in a web browser.  Requires PHP 5.3.  Tested with MySQL, but since it uses PDO, it should work with all databases that have drivers:  Oracle, PostgreSQL, SQLite, etc.

Configuration
-------------

The top of the file defines the configuration parameters required by the console:

* database url, username, password
* the maximum number of results to save in the output pane

You must at least modify the database configuration to work with your setup.
