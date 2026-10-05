<?php
header('Content-Type: text/plain'  ;

echo "PHP: " . PHP_VERSION . PHP_EOL;
echo "SAPI: " . php_sapi_name() . PHP_EOL;
echo PHP_EOL . "ODBC extension loaded: ";
var_dump(extension_loaded('odbc'  );
echo "ODBC functions:" . PHP_EOL;
var_dump(function_exists('odbc_connect'  );
var_dump(function_exists('odbc_get_available_drivers'  );

echo PHP_EOL . "Loaded extensions containing ODBC:" . PHP_EOL;

foreach (get_loaded_extensions() as $ext) {
 if (stripos($ext, 'odbc'  !== false) {
 echo $ext . PHP_EOL;
 }
}