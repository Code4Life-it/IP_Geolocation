# IP_Geolocation

PHP class to geolocate your visitors by IP address

Check given IP address geolocalization (with third part API provided by geoplugin.net).
Return geolocation data, as json array or as raw array.
Enable X-Forwarded-For mode to geolocalize IP address connecting through an HTTP proxy or load balancer.
Enable filters to get only wanted informations.

## Requirements

PHP 5.3+

## Example usage

**1. Current user IP address geolocalization**

`$geolocalization = IP_Geolocation::check();`


**2. Specific IP address geolocalization**

`$geolocalization = IP_Geolocation::check( '216.58.205.142' );`


**3. Get only continent geolocalization**

`$geolocalization = IP_Geolocation::check( '216.58.205.142', 'continent' );`
*Note: available fields are: zone/city/region/country/continent/coordinates*
  
  
**4. Enable X-Forwarded-For mode to geolocalize IP address connecting through an HTTP proxy or load balancer**
   
`$geolocalization = IP_Geolocation::check( '216.58.205.142', false, true );`
  
  
**5. Get geolocalization data as array (default is json)**
  
`$geolocalization = IP_Geolocation::check( '216.58.205.142', false, false, true );`

## Reponse example

{
    "valid":true,
    "data":{
        "zone":{
            "name":"Mountain View",
            "code":""
        },
        "city":{
            "name":"California",
            "code":"CA"
        },
        "region":{
            "name":"California",
            "code":""
        },
        "country":{
            "name":"United States",
            "code":"US"
        },
        "continent":{
            "name":"North America",
            "code":"NA"
        },
        "coordinates":{
            "lat":"37.4056",
            "lon":"-122.0775"
        }
    },
    "error":false
}
