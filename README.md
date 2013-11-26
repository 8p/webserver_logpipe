Apache Log Pipe [![SensioLabsInsight](https://insight.sensiolabs.com/projects/441e0ac2-c887-4513-9f9a-56c4ae1c6c6e/small.png)](https://insight.sensiolabs.com/projects/441e0ac2-c887-4513-9f9a-56c4ae1c6c6e)
=================

A logpipe for webservers written in php

Features
 * Rotate logfile similar to cronolog and logrotate2
 * Analyze errorlogs and accesslogs
   * calulate error frequency
   * calculate performance statistics
 * provide information for monitoring systems
   * by writing a statistics file
   * by using zabbix sender


Compile and Install
-------------------

* Get composer (http://getcomposer.org)
  mkdir composer
  curl -sS https://getcomposer.org/installer | php -- --install-dir=$PWD/composer
* Run composer to install necessary libraries:
  composer/composer.phar install
  composer install
* Create php archive (phar)
  php app/create-phar
* Run application (see examples)


Usage
-----

Features:
```
$ ./webserver_logpipe.php

./webserver_logpipe.php [-?] 

  --help                    show help
  --logfile <fileformat>    logfile with sprintf-formatstring (see: http://php.net/strftime)
  --symlink <link>          symlink location
  --cycle <sec>             how often to trigger a monitoring notification
  --precise-rotation        enable precise logfile rotation (check for rotation condition at every logline)
  --parser <type>           Enable a parser for this type of logs
                            ApacheAccesslog : parse ncsa/apache access logs
  --debug                   debug output
```

Examples:
```
./webserver_logpipe.php -?

./webserver_logpipe.php --logfile "/tmp/rz_access_log.%Y%m%d" --symlink /tmp/rz_access_log-current --cycle 3 --debug --parser ApacheAccesslog
./webserver_logpipe.php --logfile "/tmp/rz_access_log.%Y%m%d" --symlink /tmp/rz_access_log-current --cycle 3 --debug --parser ApacheErrorlog
./webserver_logpipe.php --logfile "/tmp/rz_access_log.%Y%m%d-%s" --symlink /tmp/rz_access_log-current --cycle 30 --debug 
```


Webserver configuration
-----------------------

Apache:
```
LogFormat "%h %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %D" customlog_combined
LogFormat "%{X-Forwarded-For}i %l %u %t \"%r\" %>s %b \"%{Referer}i\" \"%{User-Agent}i\" %D" customlog_proxy_combined

ErrorLog    "|php /usr/bin/logpipe.phar --logfile '/var/log/apache/rz_error_log.%Y%m%d' --symlink '/var/log/apache/rz_error_log-current' --cycle 30 --parser ApacheErrorlog"
TransferLog "|php /usr/bin/logpipe.phar --logfile '/var/log/apache/rz_access_log.%Y%m%d' --symlink '/var/log/apache/rz_access_log-current' --cycle 30 --parser ApacheAccesslog"
```

Missing features
----------------
 * tons

Testing
----------------
```
php data/simple_testcase_accesslog.php | ./webserver_logpipe.php --logfile "/tmp/rz_access_log.%Y%m%d" --symlink /tmp/rz_access_log-current --cycle 3 --debug --parser ApacheAccesslog
```


Licence and Authors
-------------------

Additional authors are very welcome - just submit your patches as pull requests.

 * Florian Preusner <florian@preusner.de>
 * Marc Schoechlin <marc.schoechlin@dmc.de>

License - see: LICENSE

Todo
-----
 * Create unittests
 * Minimize resource usage by using "strace -c ./webserver_logpipe.php" and "time ./webserver_logpipe.php"
 * Add a logger to improve the possibilities to debug this tool
 * Fix Getopt, detect wrong parameters
 * Package tool as deb and rpm format
 * Implement php error log parsing
 * Logging should always work : Improve reliability if a external monitoring inotification interface is not reachable
 * Commandline parameters should always have higher precedence


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/8p/webserver_logpipe/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

