<?php
/**
 * Digistore24 REST Api Connector
 * @author Christian Neise
 * @link https://doc.digistore24.com/api-de/
 *
 * A php class providing a connection to the Digistore24 REST api server.
 *
 * This connector is compatible with future version of the Digistore24 api.
 * Even if the Digistore24 api is extended, you still can use this connector.
 *
 * © 2015 Digistore24 GmbH, alle Rechte vorbehalten
 */

/*

Copyright (c) 2015 Digistore24 GmbH

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
associated documentation files (the "Software"), to deal in the Software without restriction,
including without limitation the rights to use, copy, modify, merge, publish, distribute,
sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or
substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if (!class_exists( 'DigistoreApi' )) {

define( 'DS_ERR_UNKNOWN',                  0 );
define( 'DS_ERR_NOT_CONNECTED',            1 );
define( 'DS_ERR_BAD_API_CALL',             2 );
define( 'DS_ERR_BAD_FUNCTION_PARAMS',      3 );
define( 'DS_ERR_NOT_FOUND',                4 );
define( 'DS_ERR_BAD_SERVER_RESPONSE',      5 );
define( 'DS_ERR_CURL',                     6 );
define( 'DS_ERR_BAD_HTTP_CODE',            7 );
define( 'DS_ERR_PERMISSION_DENIED',        8 );


define( 'DS_LOG_INFO',  'info' );
define( 'DS_LOG_ERROR', 'error' );

define( 'DS_API_WRITABLE', 'writable' );
define( 'DS_API_READONLY', 'readonly' );

class DigistoreApiException extends Exception {
}

class DigistoreApi {

    const digistore_api_connector_version = 1.1;

    /**
    * Initiates a connection to the Digistore24 api server. Note that no http call to the server is done. To actually test the connection,
    * use the api function ping()
    *
    * See api reference at https://doc.digistore24.com/api-en/
    *
    * @param string $api_key Your api key from you Digistore24 account, e.g. 123-iKWIrTsUTbCyrFuotOdV8yO20nfMI5bbrZhDCUAG
    * @throws DigistoreApiException
    */
    public static function connect( $api_key )
    {
        return new DigistoreApi( $api_key );
    }

    /**
    * Add a logger
    *
    * @param callable $callable a php callable accepting two arguments: $loglevel, $message - e.g. function my_logger( $loglevel, $message )
    * @return int handle to remove logger (using removeLogger())
    */
    public function addLogger( $callable )
    {
        $handle = $this->logger_index++;
        $this->loggers[ $handle ] = $callable;
        return $handle;
    }

    /**
    * Remove a logger
    *
    * @param int $handle as returned by addLogger()
    */
    public function removeLogger( $handle )
    {
        unset( $this->loggers[ $handle ] );
    }

    /**
    * Set an operator name - a person, who is responsible for the changes performed via the api call.
    * The name is only used for logging purposes.
    *
    * @param string $operator_name  your application's user name (NOT a Digistore24 name)
    */
    public function setOperator( $operator_name )
    {
        $this->operator_name = $operator_name;
    }

    /**
    * Sets the language for the api's messages.
    * By default all messages are in the language set in the Digistore24 account of the api key owner.
    *
    * @param string $language 'de' or 'en'
    */
    public function setLanguage( $language )
    {
        $tokens = explode( '_', $language );

        $language = $tokens[0];

        $is_language_valid = in_array( $language, $this->_validLangs() );

        if ($is_language_valid)
        {
            $this->language = $language;
        }
    }

    /**
    * Destroys the connection to the server.
    *
    */
    public function disconnect()
    {
        $this->api_key  = false;
    }

    /**
    * Execute api function on the Digistore24 server
    *
    * @param string $function_name
    * @param array $arguments
    * @throws DigistoreApiException
    */
    public function __call( $function_name, $arguments )
    {
        return $this->_exec( $function_name, $arguments );
    }

    /**
    * Used for debug purposes. Returns the most recently used api url called.
    */
    public function getLastUrl()
    {
        if ($this->last_url===false) {
            return false;
        }

        $querystring = http_build_query( $this->last_params, '', '&' );


        return $this->last_url . '?' . $querystring;
    }

    /**
    * For debugging purposes only
    *
    * @param string $url
    */
    public function setBaseUrl( $url='https://www.digistore24.com' ){
        $this->base_url = $url;
    }

    private $api_key  = '';
    private $language = '';
    private $operator_name = '';
    private $loggers  = array();
    private $logger_index = 1;
    private $base_url = 'https://www.digistore24.com';
    private $last_url    = false;
    private $last_params = false;


    private function __construct( $api_key )
    {
        $this->api_key  = $api_key;
    }

    private function _log( $level, $msg, $arg1='', $arg2='', $arg3='' )
    {
        if (empty($this->loggers)) {
            return;
        }

        $msg = sprintf( $msg, $arg1, $arg2, $arg3 );

        foreach ($this->loggers as $one)
        {
            call_user_func( $one, $level, $msg );
        }
    }

    private function _error( $code, $arg1='', $arg2='', $arg3='' )
    {
        $msg = $this->_errorMsg( $code, $arg1, $arg2, $arg3 );
        $this->_log( DS_LOG_ERROR, $msg );
        throw new DigistoreApiException( $msg, $code );
    }

    private function service_url()
    {
        $key  = $this->api_key;

        if (!$key) {
            $this->_error( DS_ERR_NOT_CONNECTED );
        }

        $base_url = $this->base_url;

        return "$base_url/api/call/$key/json/";
    }

    private function _exec( $function_name, $arguments )
    {
        if (!$this->api_key) {
            $this->_error( DS_ERR_NOT_CONNECTED );
        }

        $this->_log( DS_LOG_INFO, "Call to '%s' - started", $function_name . '()' );

        if (!$function_name || !is_array($arguments)) {
            $this->_error( DS_ERR_BAD_FUNCTION_PARAMS, "$function_name()" );
        }

        $url = $this->service_url() . $function_name;

        $args = array();
        foreach ($arguments as $index => $one)
        {
            $key = 'arg' . ($index+1);

            $this->_set_post_param( $args, $key, $one );
        }

        $args['language'] = $this->language;
        $args['operator'] = $this->operator_name;
        $args['ds24ver' ] = self::digistore_api_connector_version;

        $data = $this->_http_request( $url, $args );

        $this->_log( DS_LOG_INFO, "Call to %s - completed", $function_name . '()' );

        return $data;
    }


    private function _http_request( $url, $params, $settings = array() )
    {
        $this->last_url    = $url;
        $this->last_params = $params;

        $querystring = http_build_query( $params, '', '&' );

        $headers = array (
                        'Content-type: application/x-www-form-urlencoded; charset=utf-8',
                        'Accept-Charset: utf-8',
                   );

        if (!function_exists('curl_init')) {
            $this->_error( DS_ERR_CURL,  $ch_error_no=0, $ch_error_msg='PHP module Curl is required. Please ask your web admin to enable it.' );
        }

        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP|CURLPROTO_HTTPS);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
        curl_setopt( $ch, CURLOPT_USERAGENT, 'DigiStore-API-Connector/1.0 (Linux; en-US; rv:1.0.0.0) php/20130430 curl/20130430' );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_URL, $url);
        curl_setopt( $ch, CURLOPT_POST, count($params));
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $querystring);
        curl_setopt( $ch, CURLOPT_ENCODING, "" );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

        $contents  = curl_exec($ch);

        $http_code = ''.curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $ch_error_no  = curl_errno($ch);
        $ch_error_msg = curl_error($ch);

        @curl_close($ch);

        if ($ch_error_msg)
        {
            $this->_error( DS_ERR_CURL,  $ch_error_no, $ch_error_msg );
        }

        $is_http_call_success = $http_code == 200;
        if (!$is_http_call_success)
        {
            $this->_error( DS_ERR_BAD_HTTP_CODE, $http_code );
        }


        $result = @json_decode( $contents );

        $debug_info = '';
        if (!isset($result))
        {
            global $DM_SUPPORT_URL_TEST;

            $must_report = (defined('NCORE_DEBUG') && NCORE_DEBUG)
                        || !empty($DM_SUPPORT_URL_TEST);

            if ($must_report) {

                ob_start();
                echo "<pre>URL: $url\nQuery: $querystring\nParams: ";
                print_r( $params );
                echo "\nResponse:</pre>$contents";
                $debug_info = ob_get_clean();

                $debug_info = str_replace( $this->api_key, 'DS24_APIKEY_PROTECTED', $debug_info );

                trigger_error( "Invalid digistore24 api server response: $debug_info" );
            }
        }

        $success = $result && is_a($result,'stdClass') && isset($result->api_version) && isset($result->result);

        if ($success)
        {
            $api_version = $result->api_version;
            $result_type = $result->result;

            switch ($result_type)
            {
                case 'error':
                    $msg  = $result->message;
                    $code = $result->code;
                    throw new DigistoreApiException( $msg, $code );

                case 'success':
                    $data = $result->data;
                    return $data;
            }
        }

        $this->_error( DS_ERR_BAD_SERVER_RESPONSE, $contents );
    }

    /**
    * Translates an error code in a human readable message for your application's users.
    *
    * @param int $code  a DS_ERR_XXXXX constant
    */
    private function _errorMsg( $code, $arg1='', $arg2='', $arg3='' )
    {
        $error_messages  = $this->_errorMsgList();

        if ($code==='test') {
            $is_lang_valid = isset( $error_messages[ $this->language ] );
            return $is_lang_valid;
        }

        $msgs = isset( $error_messages[ $this->language ] )
              ? $error_messages[ $this->language ]
              : $error_messages[ 'en' ];

        $msg = isset( $msgs[ $code ] )
             ? $msgs[ $code ]
             : $msgs[ DS_ERR_UNKNOWN ];

        return sprintf( $msg, $arg1, $arg2, $arg3 );
    }

    private function _validLangs() {
        return array_keys( $this->_errorMsgList() );
    }

    private function _errorMsgList()
    {
        return array(
            'de' => array(
                DS_ERR_UNKNOWN                   => 'Unbekannter Fehler!',
                DS_ERR_NOT_CONNECTED             => 'Nicht zum Digistore24-Server verbunden.',
                DS_ERR_BAD_API_CALL              => 'Die Verbindungsparameter sind ungültig.',
                DS_ERR_BAD_FUNCTION_PARAMS       => 'Ungültige Parameter bei Funktionsaufruf %s.',
                DS_ERR_BAD_SERVER_RESPONSE       => 'Der Digistore24-Server hat eine ungültige Antwort geliefert. (Technische Information: %s)',
                DS_ERR_CURL                      => 'Fehler beim HTTP-Aufruf durch CURL (#%s - %s)',
                DS_ERR_BAD_HTTP_CODE             => 'Der Digistore24-Server lieferte eine Antwort mit einem Ungültigen HTTP-Code (%s)',
            ),
            'en' => array(
                DS_ERR_UNKNOWN                   => 'Unknown error!',
                DS_ERR_NOT_CONNECTED             => 'Not connected to the Digistore24 server.',
                DS_ERR_BAD_API_CALL              => 'Invalid connection parameters.',
                DS_ERR_BAD_FUNCTION_PARAMS       => 'Invalid parameters for function call %s.',
                DS_ERR_BAD_SERVER_RESPONSE       => 'The Digistore24 server delivered an invalid response. (Technical information: %s)',
                DS_ERR_CURL                      => 'Http call error reported by curl (#%s - %s)',
                DS_ERR_BAD_HTTP_CODE             => 'The Digistore24 server responded with an invalid http code (%s)',
            )
        );
    }

    function _set_post_param( &$args, $key, $value )
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            $args[ $key ] = $value;
            return;
        }

        foreach ($value as $one_key => $one_value)
        {
            $one_name = $key . '[' . $one_key . ']';
            $this->_set_post_param( $args, $one_name, $one_value );
        }

    }

}

} // if class_exists
