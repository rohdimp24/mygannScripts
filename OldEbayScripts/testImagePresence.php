<?php
/**
 * Created by PhpStorm.
 * User: rohitagarwal
 * Date: 2/11/15
 * Time: 6:21 AM
 */

//$webfile="http://onemvweb.com/mvebay/T-203.jpg";
$webfile="http://onemvweb.com/mvebay/T-90C.jpg";

//$fp = @fopen($webfile, "r");
//if ($fp !== false)
//
if (@GetImageSize($webfile))
{
    echo "image found";
}
else
    echo "image not found";
fclose($fp);
