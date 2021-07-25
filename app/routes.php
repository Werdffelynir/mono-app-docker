<?php

use Lib\Core\Router;

# ######################################################################################
# WEB
# ################
Router::get('/', function () {
    echo '[router:main]';
});

# ######################################################################################
# API
# ################
const PREFIX = '/api';
Router::get(PREFIX.'/', function () {
    echo '[router:api]';
});



Router::run();
