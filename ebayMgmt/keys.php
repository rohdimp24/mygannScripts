<?php

/*  © 2013 eBay Inc., All Rights Reserved */
/* Licensed under CDDL 1.0 -  http://opensource.org/licenses/cddl1.php */
//show all errors
error_reporting(E_ALL);
// these keys can be obtained by registering at http://developer.ebay.com

$production         = true;   // toggle to true if going against production
$compatabilityLevel = 927;    // eBay API version

if ($production) {
    $devID = '9a526ef7-463e-4865-a975-2aa52b8f02d1';   // these prod keys are different from sandbox keys
    $appID = 'AVirji76c-212a-4502-8e93-ea765218610';
    $certID = '25ec50a2-b3d6-4785-be4c-ead3fcc59c3d';
    //set the Server to use (Sandbox or Production)
    $serverUrl = 'https://api.ebay.com/ws/api.dll';      // server URL different for prod and sandbox
    //the token representing the eBay user to assign the call with
    $userToken = 'AgAAAA**AQAAAA**aAAAAA**XtOUVQ**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wJkYuoDJOHoQ6dj6x9nY+seQ**wugCAA**AAMAAA**FwFnX9M6QmadloODQpkcYZ+KVJ85Ba4RIDnu0wRtpCRhUbiXtc9CE7I1oJ8qYN5QnffgkTdXwp9ebv+Zd8T1Nyz2aiJiGbSSlQYl2LH62nTy4kKVfz5eS1b4Bd03AntnVpX7/amDNTMsJEZIJ1G8jAKOLopAuJRXkBLtBsgDool0hTiMOjvDtBh3rl4X4YurHr7qMLQE8VArdTewHjKv63GbdDvOMnZJkuZeug4i+sblOQaS8RPo0zYqCJfDXW+UqQgQ7f65dAwNJhiO2KlBUKUlog9gMD7qFXkcK3l/RKAXHqq4ZmXW3jWLICOrhKLWuIwowxNyaQD71Q5ZsdxgyqHRg1Rf1Cstd+J6oZmTDM3QOJel2dEfJLA5L2Zoq5lD3Vx+xR20/IqZYQLgAf4Zs5F18Io9M+peegbJQLwY7L2v8f0WLjgDO70R01D5yF1wcncNZ8C/pgWQVOAFhy8t22Y1ofQ5hdJW/phSKx+ttslUolrBt7NdhY1JAhss6kImAGOXJA3YEhS2mrU6AROoGJLVi/5/lE87WauRJNm3ZLpQNillO5u+xw/3hO1KnJN5PVS+T57yfdOwl3o9u2zBTllXYDXTDDaA/q93yXhxpMslXcj8Oc87myWqniFJmqhzQqzb/2bPRTINB444P75BR8byYOb6zbCVqSK8ZYlaYflmFUpqVRv+N6CbhNpWnmPHr7Ie5SX7rRzG3fPurB6LBK0+QC9JhYmVEhXHelYOSUwNtc3JFuxG6EPcyYR33vSh';
} else {
    // sandbox (test) environment
    $devID = 'xxxxxxxx';   // these SB keys are different from prod keys
    $appID = 'xxxxxxxxx';
    $certID = 'xxxxxxxxxxxxxx';
    //set the Server to use (Sandbox or Production)
    $serverUrl = 'https://api.sandbox.ebay.com/ws/api.dll';
    // the token representing the eBay user to assign the call with
    // this token is a long string - don't insert new lines - different from prod token
    $userToken = '*************';
}


?>