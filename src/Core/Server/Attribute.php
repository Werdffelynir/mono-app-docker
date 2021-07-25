<?php


namespace Lib\Core\Server;


class Attribute
{
    const DOCUMENT_ROOT = 'DOCUMENT_ROOT'; // string
    const REMOTE_ADDR = 'REMOTE_ADDR'; // string
    const REMOTE_PORT = 'REMOTE_PORT'; // string
    const SERVER_SOFTWARE = 'SERVER_SOFTWARE'; // string
    const SERVER_PROTOCOL = 'SERVER_PROTOCOL'; // string
    const SERVER_NAME = 'SERVER_NAME'; // string
    const SERVER_PORT = 'SERVER_PORT'; // string
    const REQUEST_URI = 'REQUEST_URI'; // string
    const REQUEST_METHOD = 'REQUEST_METHOD'; // string
    const SCRIPT_NAME = 'SCRIPT_NAME'; // string
    const SCRIPT_FILENAME = 'SCRIPT_FILENAME'; // string
    const PATH_INFO = 'PATH_INFO'; // string
    const PHP_SELF = 'PHP_SELF'; // string
    const HTTP_HOST = 'HTTP_HOST'; // string
    const HTTP_CONNECTION = 'HTTP_CONNECTION'; // string
    const HTTP_PRAGMA = 'HTTP_PRAGMA'; // string
    const HTTP_CACHE_CONTROL = 'HTTP_CACHE_CONTROL'; // string
    const HTTP_SEC_CH_UA = 'HTTP_SEC_CH_UA'; // string
    const HTTP_SEC_CH_UA_MOBILE = 'HTTP_SEC_CH_UA_MOBILE'; // string
    const HTTP_DNT = 'HTTP_DNT'; // string
    const HTTP_UPGRADE_INSECURE_REQUESTS = 'HTTP_UPGRADE_INSECURE_REQUESTS'; // string
    const HTTP_USER_AGENT = 'HTTP_USER_AGENT'; // string
    const HTTP_ACCEPT = 'HTTP_ACCEPT'; // string
    const HTTP_SEC_FETCH_SITE = 'HTTP_SEC_FETCH_SITE'; // string
    const HTTP_SEC_FETCH_MODE = 'HTTP_SEC_FETCH_MODE'; // string
    const HTTP_SEC_FETCH_USER = 'HTTP_SEC_FETCH_USER'; // string
    const HTTP_SEC_FETCH_DEST = 'HTTP_SEC_FETCH_DEST'; // string
    const HTTP_ACCEPT_ENCODING = 'HTTP_ACCEPT_ENCODING'; // string
    const HTTP_ACCEPT_LANGUAGE = 'HTTP_ACCEPT_LANGUAGE'; // string
    const HTTP_COOKIE = 'HTTP_COOKIE'; // string
    const REQUEST_TIME_FLOAT = 'REQUEST_TIME_FLOAT'; // float
    const REQUEST_TIME = 'REQUEST_TIME'; // int
}