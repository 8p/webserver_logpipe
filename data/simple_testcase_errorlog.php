#!/usr/bin/env php
<?php

for ($i = 1; $i <= 25000; $i++) {
   echo '[Wed May 29 09:27:19 2013] [error] [client 192.168.26.151] Directory index forbidden by Options directive: /export/home/WWW/foobar-ahha/htdocs/typo3temp/
[Wed May 29 09:27:21 2013] [error] [client 192.168.26.151] Directory index forbidden by Options directive: /export/home/WWW/foobar-ahha/htdocs/fileadmin/templates/LALA/lala_de/css/
[18-Jul-2013 16:15:01] PHP Fatal error:  Class name must be a valid object or a string in /export/home/WWW/foobar-ahha/htdocs/typo3conf/ext/dmc_mb3_connector_core/facades/isearch_core.lib on line 159
[18-Jul-2013 16:17:08] PHP Warning:  htmlentities() expects parameter 1 to be string, array given in /export/home/WWW/foobar-ahha/htdocs/typo3conf/ext/dmc_mb3_integratedsearch/pi1/class.tx_dmcmb3integratedsearch_pi1.php on line 442
[18-Jul-2013 16:19:55] PHP Fatal error:  Uncaught exception \'UnexpectedValueException\' with message \'Failed to restore the session token from the registry.\' in /export/home/WWW/foobar-ahha/gack1_1_src/t3lib/formprotection/class.t3lib_formprotection_backendformprotection.php:175
Stack trace:
#0 /export/home/WWW/foobar-ahha/gack1_1_src/typo3/classes/class.ajaxlogin.php(50): t3lib_formprotection_BackendFormProtection->setSessionTokenFromRegistry()
#1 [internal function]: AjaxLogin->login(Array, Object(TYPO3AJAX))
#2 /export/home/WWW/foobar-ahha/gack1_1_src/t3lib/class.t3lib_div.php(5146): call_user_func_array(Array, Array)
#3 /export/home/WWW/foobar-ahha/gack1_1_src/typo3/ajax.php(73): t3lib_div::callUserFunction(\'typo3/classes/c...\', Array, Object(TYPO3AJAX), false, true)
#4 {main}
  thrown in /export/home/WWW/foobar-ahha/gack1_1_src/t3lib/formprotection/class.t3lib_formprotection_backendformprotection.php on line 175';
}
