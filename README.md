f-droid-web
===========

A simple and lightweight webpage which aims is to present the apps stored in a f-droid repository

DISCLAIMER:

-- This is a work in progress --


INSTALLATION:

Just put the code inside the repo directory and make your webserver pointing to it.


CONFIGURATION:

All the configuration stuffs are in the top of the index.php file.

define('HASH_ALGO', 'whirlpool');	// The hash algorithm used to check the index.xml file  
define('USE_QRCODE', true);			// Define if you want to use (or not) the qr-codes. (incase that you don't want to use them, you don't need the phpqrcode dir  
define('NUMBER_LAST_APP', 4);		// Define the number of last apps appearing in the right box  
define('RECORDS_PER_PAGE', 3);		// Define the number of apps per page  
define('DEFAULT_LANG', 'fr');		// Define the default language used by the GUI, actually the values are fr: french, en: english and es: spanish  
define('LOCALIZATION', 'fr');		// Define the language used to describe the apps informations  
define('MSG_FOOTER', 'Your footer here');		// Define the message in the footer  

The description of the repository and the URI of the logo come from the index.xml. For the logo, make sure, you put it in a directory where your web-server can reach it.


DESCRIPTION:

Directory hierarchy:  

root/  

     Media/  

          images/  

          css/  

     cache/ [Contains the cached data (serialized PHP structures)]  

     lang/ [Contains the localized files for the GUI]  
          dict/ [Contains stopwords list and several function to the search by words]  

     phpqrcode/ [PHP library which generate the qr-codes (made by Dominik Dzienia <deltalab at poczta dot fm>)]  

     qrcodes/ [Contains the generated qr-codes]  

     index.php [Main file]  


REMARK:

* This website don't use cookies (except the session cookie). It's tracker free.
For the moment, it didn't use database or nosql things just plaintext files or files storing serialized PHP structures.

* The website is HTML5 and CSS3 valid.

* Actually, you can browse by apps, by licenses or by categories.

* To add a translation, simply add a code_lang.php wich must be a copy of one other language file in ./lang directory
There is also a translation for categories.


TODO:

* Search apps on summary, name and desc (nearly finished)
* JSONification 
* UI dynamization (JS)
* Add other languages (german, portugese, ...)
* WCAG2.0 AAA

