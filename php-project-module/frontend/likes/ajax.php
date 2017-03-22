<?php

use progorod\routing\Request;
use progorod\routing\responses\JsonResponse;
use redesign\pageblocks\common\likes\BLikesController;


try {
    $response = (new BLikesController())
        ->handleRequest(Request::create());
} catch (Exception $err) {
    $response = new JsonResponse();
    $response->fail($err->getCode(), $err->getMessage());
}

echo $response->toString();
