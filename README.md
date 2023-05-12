Retrieve data by curl and include into website.
Just insert url into "url" param => the module will display the content of the external page in the module.

* ![image](https://github.com/hoochicken/mod_qlcurl/assets/11626291/5ddee786-0f75-4011-be14-b979f4dda088)

Settings in detail:

* Modul
  * Url => url of website whose content shall be displayed
  * Connection type, choose between curl and file_get_contents()
  * user agent: any string, default is 'qlcurlbot'
* Display
  * Display additionally url
  * Display inwthin textara (e. g. for json to ease copying)
  * Xml transform: whether the string shall be interpreted as xml
* Login: if website is secured vis htass, enter user and login

