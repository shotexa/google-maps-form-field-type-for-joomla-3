<?php
/**
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @author      <shota jolbordi>
 * @email       <shota1748@gmail.com>
 *
 */
defined('JPATH_PLATFORM') or die;

/**
 *
 *
 *
 */
class JFormFieldgmap extends JFormField
{

    /**
     * The form field type.
     *
     * @var    string
     * @since  11.1
     */
    protected $type = 'gmap';

    /**
     * Method to get the all the nessesery components and return marcup
     *
     *
     * @return  string  The field input markup.
     *
     *
     */
    protected function getInput()
    {

        $params = $this->getParams();

        $doc = JFactory::getDocument();
        $html = $this->getHTML($params);
        $js = $this->getJs($params);
        $doc->addScript('http://maps.googleapis.com/maps/api/js?key=' . $params->key);
        $doc->addScriptDeclaration($js);

        return $html;
    }


    /**
     * Method to get Form field parameters
     *
     * @return StdClass object
     *
     */
    public function getParams()
    {
        $params = new stdClass();
        $params->id = $this->id;
        $params->name = $this->name;


        $logitude = $this->getAttribute('longitude');
        $latitude = $this->getAttribute('latitude');
        if (empty( $this->value ) || ( empty( $logitude ) || empty( $latitude ) ))
        {
            $this->setDefaultLocation($params);
        }
        if ($this->isJason($this->value))
        {
            $attrs = json_decode($this->value);
            $params->latitude = $attrs->latitude;
            $params->longitude = $attrs->longitude;
            $params->addres = $attrs->addres;
        }
        else
        {
            $this->setDefaultLocation($params);
        }
        $key = $this->getAttribute('key') ? $this->getAttribute('key') : 'AIzaSyCoDjF5GCTLPwUWBrFPG6e98anH7yiDjmI';
        $params->key = $key;
        $width = $this->getAttribute('widht') ? $this->getAttribute('width') : '400px';
        $params->width = $width;
        $height = $this->getAttribute('height') ? $this->getAttribute('height') : '300px';
        $params->height = $height;
        $params->valueAsJson = json_encode(array(
                                               'longitude' => $params->longitude,
                                               'latitude'  => $params->latitude,
                                               'addres'    => $params->addres
                                           ));

        return $params;
    }

    private function setDefaultLocation(&$params)
    {
        $params->latitude = 41.710812;
        $params->longitude = 44.774945;
        $params->addres = 'Ateni Street, Tbilisi, Georgia 18'; // Hardcode smart web studio coordinats if they are not set properly
    }

    private function isJason($value)
    {
        return ( ( is_string($value) && ( is_object(json_decode($value)) || is_array(json_decode($value)) ) ) ) ? true : false;
    }


    /**
     * Method to get HTML marcup
     *
     * @return string
     */
    protected function getHTML($params)
    {
        $html[] = '<div id = "gmap" style = "margin-bottom:10px; height:' . $params->height . '; width :' . $params->width . ';"></div>';
        $html[] = '<input type = "text" class = "inputbox" name = "fakename" id = "fakename" value = "' . $params->addres . '"/>';
        $html[] = '<input type = "hidden" name = "' . $params->name . '" id = "' . $params->id . '" value = \'' . $params->valueAsJson . '\' />';
        $html[] = '<input type = "button" id = "findelocation" value = "' . JText::_('Find') . '" class = "btn" style = "margin-left : 5px" />';

        return implode('', $html);
    }

    protected function getJs($params)
    {
        $js = <<<__JS__
            $ = jQuery;
            $(document).ready(function () {
                var location = {lat: $params->latitude, lng: $params->longitude};
                var map = new google.maps.Map(document.getElementById('gmap'), {
                    center: location,
                    zoom: 15
                });
                var geocoder = new google.maps.Geocoder();
                var marker = new google.maps.Marker({
                    position: location,
                    map: map
                });   

                google.maps.event.addListener(map, 'click', function (event) {
                    placeMarker(event.latLng);
                });
                //var button = document.getElementById('findelocation');
                
                $("#findelocation").click(function(){
                    geocodeAddress(geocoder, map);
                });
                
                
                /*
                 this function searches for the place on the map from addres name 
                 */
                function geocodeAddress(geocoder, resultsMap) {
                    var address = document.getElementById('fakename').value;
                    geocoder.geocode({'address': address}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK)
                        {
                            var location = results[0].geometry.location;
                            resultsMap.setCenter(location);
                            getAddresFromLatLng(location, function(addres){
                                updateFakeBox(addres);
                                updateRealValues(location.lat(), location.lng(), addres);
                            });
                            marker.setPosition(results[0].geometry.location);
                        } else
                        {
                            address.value = '';
                        }
                    });
                }

                function placeMarker(location) {
                    if (marker == undefined) {
                        marker = new google.maps.Marker({
                            map: map
                        });
                        getAddresFromLatLng(location, function (addres) { // you get addres string in addres parameter
                            populateState(marker, location, addres);
                        });

                    } else {
                        getAddresFromLatLng(location, function (addres) { // you get addres string in addres parameter
                            populateState(marker, location, addres);
                        });

                    }
                    map.panTo(location);
                }

                function populateState(marker, location, addres)
                {
                    marker.setPosition(location);
                    updateRealValues(location.lat(), location.lng(), addres);
                    updateFakeBox(addres);
                }
                function updateFakeBox(value)
                {
                    document.getElementById('fakename').value = value;
                }
                function updateRealValues(latitude, longitude, addres)
                {
                    var realValue = document.getElementById('$params->id'); //hardcode for now
                    var location = {
                        latitude: latitude,
                        longitude: longitude,
                        addres: addres
                    };
                    var locationString = JSON.stringify(location);
                    realValue.value = locationString;
                }
                function getAddresFromLatLng(latLng, callback)
                {
                    geocoder.geocode({'location': latLng}, function (results, status) {
                        if (status === google.maps.GeocoderStatus.OK) {
                            callback(results[1].formatted_address);
                        } else {
                            callback('Ateni Street, Tbilisi, Georgia 18');
                        }
                    });

                }
            });
__JS__;

        return $js;
    }

}
