<?php
/**
 * IP_Geolocation
 * Check given IP address geolocalization (with third part API provided by geoplugin.net).
 * Return geolocation data, as json array or as raw array.
 * Enable X-Forwarded-For mode to geolocalize IP address connecting through an HTTP proxy or load balancer.
 * Enable filters to get only wanted informations.
 *
 * Example usage:
 *
 * 1. Current user IP address geolocalization
 * $geolocalization = IP_Geolocation::check();
 *
 * 2. Specific IP address geolocalization
 * $geolocalization = IP_Geolocation::check( '216.58.205.142' );
 *
 * 3. Get only continent geolocalization
 * $geolocalization = IP_Geolocation::check( '216.58.205.142', 'continent' );
 * Note: available fields are: zone/city/region/country/continent/coordinates
 *
 * 4. Enable X-Forwarded-For mode to geolocalize IP address connecting through an HTTP proxy or load balancer 
 * $geolocalization = IP_Geolocation::check( '216.58.205.142', false, true );
 *
 * 5. Get geolocalization data as array (default is json)
 * $geolocalization = IP_Geolocation::check( '216.58.205.142', false, false, true );
 * 
 * @since       1.0.0
 * @author      Code4Life.it <supporto@code4life.it>
 * @copyright   2019 Code4Life
 * @version     1.0.0
 * @package     IPG
 * @see         http://www.geoplugin.net/
 */

class IP_Geolocation {

    /**
     * @var string  $ip         Contains a valid IP address
     * @var string  $filters    Contains field to retrieve
     * @var bool    $xff        Contains X-Forwarded-For mode enabled or not
     */
    protected $ip, $filters, $xff;

    /**
     * @var array  $output      Contains class response output
     */
    protected $output = array();

    /**
     * @var object  $instance   Contains current class instance
     */
    private static $instance = null;



    /**
     * Check IP geolocalization.
     * @since   1.0.0
     * @return  mixed
     */
    public static function check() {
        return call_user_func_array( array( self::instance(), 'init' ), func_get_args() );
    }

    /**
     * Get IP_Geolocation class instance.
     * @since   1.0.0
     * @return  object
     */
    protected static function instance() {
        if ( self::$instance === null ) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Inizialize geolocalization.
     * @since   1.0.0
     * @param   string  $ip     IP address to geolocalize. If omitted, get current visitor IP address
     * @param   string  $filter Fields to return
     * @param   bool    $xff    Enable X-Forwarded-for mode
     * @param   bool    $raw    Throw response as array or json
     * @return  mixed   Geolocalization response
     */
    protected function init( $ip = false, $filters = false, $xff = false, $raw = false  ) {
        $this->ip       = $ip;
        $this->filters  = $filters;
        $this->xff      = $xff;

        $this->validate() && $this->geolocalize();

        return $raw ? $this->output : json_encode( $this->output );
    }

    /**
     * Validate IP address.
     * @since   1.0.0
     * @return  bool    IP address is valid or not
     */
    protected function validate() {
        if ( $this->ip === false ) {
            $this->ip = $_SERVER['REMOTE_ADDR'];

            if ( $this->xff ) {
                if ( filter_var( @$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP ) ) {
                    $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                }
                if ( filter_var( @$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
                    $this->ip = $_SERVER['HTTP_CLIENT_IP'];
                }
            }
        }

        if ( filter_var( $this->ip, FILTER_VALIDATE_IP ) === false ) {
            return $this->output( false, false, 'IP address invalid or malformed' );
        }

        if ( in_array( $this->ip, array( '::1', '127.0.0.1' ) ) ) {
            return $this->output( false, false, 'IP address refer to localhost' );
        }

        return true;
    }

    /**
     * Retrieve geolocalization data.
     * @since   1.0.0
     * @return  bool    Geolocalization has valid data or not
     */
    protected function geolocalize() {
        $response = @json_decode( file_get_contents( 'http://www.geoplugin.net/json.gp?ip=' . $this->ip ) );
        $data = array(
            'zone'          => array(
                'name'      => @$response->geoplugin_city,
                'code'      => ''
            ),
            'city'          => array(
                'name'      => @$response->geoplugin_regionName,
                'code'      => @$response->geoplugin_regionCode
            ),
            'region'        => array(
                'name'      => @$response->geoplugin_region,
                'code'      => ''
            ),
            'country'       => array(
                'name'      => @$response->geoplugin_countryName,
                'code'      => @$response->geoplugin_countryCode
            ),
            'continent'     => array(
                'name'      => @$response->geoplugin_continentName,
                'code'      => @$response->geoplugin_continentCode
            ),
            'coordinates'   => array(
                'lat'       => @$response->geoplugin_latitude,
                'lon'       => @$response->geoplugin_longitude
            )
        );

        if ( ! $this->filters ) {
            return $this->output( true, $data, false );
        }


        if ( ! array_key_exists( $this->filters, $data ) ) {
            return $this->output( false, false, 'Requested field is invalid or not available' );
        }
        
        return $this->output( true, $data[$this->filters], false );
    }

    /**
     * Format class returned output.
     * @since   1.0.0
     * @param   bool    $valid
     * @param   array   $data
     * @param   bool    $error
     * @return  bool    Geolocalization has valid data
     */
    protected function output( $valid, $data, $error ) {
        $this->output = array(
            'valid' => $valid,
            'data' => $data,
            'error' => $error
        );

        return $valid;
    }

}
