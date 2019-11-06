<?php

use App\Controllers\ShoutController;

$app->group('/shout/', function () {
    $this->get('{person}', [getContainer()->get(ShoutController::class), 'shout']);
});
