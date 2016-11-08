Google Maps form field type for joomla 3.0
====================



##description 

Form field allows you to easily select location in extensions. you will get value of this form field in JSON format.
examle:

```json
{"longitude": <longitude of selected location>,"latitude": <latitude of selected location> ,"addres":<"addres of selected location in english">}
```




## Installation



### local installation (custom component only)
If you want to install this form field locally for your custom component 
grab gmap.php and put it in your component's administrator part in models/fields directory


If you want to have form field type globally accessible in all extensions put gmap.php 
in libraries/joomla/form/fields directory

## Usage

1. `type` (mandatory) must be "gmap"
2. `name` (mandatory) is the unique name of the parameter.
3. `label` (mandatory) (translatable) is the descriptive title of the field.
4. `description` (optional) (translatable) is text that will be shown as a tooltip when the user moves the mouse over the label.
5. `longitude` (optional) default longitude
6. `latitude` (optional) default latitude
7. `key` (mandatory) google maps javascript API key
8. `widht` (optional) optional, width of rendered google maps
9. `height` (optional) height of rendered google maps

####example

```xml
<field
    type ="gmap" 
    name ="mygmap" 
    label = "Select location" 
    description = "click to select location"
    longitude = "40.423452" 
    latitude = "41.253335"  
    key = "AIzaSyCoDjF5GCTLPwUWBrFPG6e38anH7yiDjmI"
    widht = "100px" 
    height = "200px" 
</field>
```
