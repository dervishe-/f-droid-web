f-droid-web
===========

DESCRIPTION:

__F-droid-web__ is a simple and lightweight web interface to [f-droid server] (https://gitlab.com/fdroid/fdroidserver/tree/master). It provide a way: 
* via qr-codes to register automatically the repository in the [fdroid app](https://f-droid.org/repository/browse/?fdfilter=f-droid&fdid=org.fdroid.fdroid)
* to browse your f-droid catalogue by
   * app's name, summary and description
   * app's type of license
   * app's category

For the installation and configurations information, go to the [wiki](https://github.com/dervishe-/f-droid-web/wiki)

THANKS TO:

* [azrdev](https://github.com/azrdev) we now have a mostly complete german language pack
* [Peter Serwylo](https://github.com/pserwylo) we will soon have a complete templating system in order to personnalize the UI

MISC:

* This website don't use cookies (except the session cookie). It's tracker free. For the moment, it didn't use database or nosql things just plaintext files or files storing serialized PHP structures.
* It is HTML5 and CSS3 valid (with semantic markup).
* The website interface is WCAG2.0 AA compliant.
* You can browse by apps, by licenses or by categories.
* You can search by words. The search will be performed in the name, summary and description fields. You can use the "+" symbol, in order to cross research:
word1 + word2 will gather apps matching the 2 words (or n words).
* The last apps list is available via an atom feed.
* You can make your queries both by GET and POST method.
* You can retrieve informations in JSON format.
* You can make your search available via a personnalized Atom feed

TODO:

* Add other languages (italian, portugese, polish, ...)
* WCAG2.0 AAA
* Translating the android permissions in several languages
