#Version
3.2.0

#Changes
##3.2.0 | 2015-07-08 09:05
* Fixed: Several little bugs fixed
* Feature: Model::toArray(), objectToArray alis.
* Feature: objectToArray() now accepts string as $key to return all minus. 

##3.1.1 | 2015-05-28 12:01
* Fixed: Twig will now look for plugin templates also.

##3.1.0 | 2015-05-11 14:15
* Fixed: Various Plugin loading, rewrite and routing issues.
    
##3.0.5 | 2015-02-25 20:05
* Fixed: Quick query constructor now actually checks NULL value instead of skipping it.

##3.0.4 | 2015-02-25 13:00
* Feature: Model::all(), return all objects.

##3.0.3 | 2015-02-21 14:32
* Feature: Template::getUrl('last'), returns last value of url.
* Feature: Template::getView(), returns current view.

##3.0.2 | 2015-02-12 19:41
* Feature: Module Login accepts static $className for extending. To determin which class
          to use for users.

##3.0.1 | 2015-02-07 15:29
* Fixed: Update object where primary key = value instead of default id. Combinations possible.
* Fixed: CSRF::generateFormData() returns string instead of echo.
* Fixed: Query::where() param binding replacement for '`'.

##3.0.0 | 2015-01-24 17:09
* Feature: Plugins!

##2.4.0 | 2015-01-21 20:48
* Feature: cfile_get_contents($url, $json, $opts), replacement for file_get_contents with cURL.
          Return JSON or string, set additional CURLOPT_ options.
* Feature: rfile_get_contents($url), read contents of a file.
* Feature: TemplateMailer, send HTML mail templates with replaceable variabels and included stylesheets.

##2.3.2 | 2015-01-14 10:01
* Fixed: At any time, correctly include load.php with dirname(__FILE__). Useful for cronjobs.
* Fixed: Define CW_CRON and include www/index.php for use with cronjobs.

##2.3.1 | 2015-01-07 14:10
* Feature: Initialize Query object with quick where statment, in favour of complete sql.
    
##2.3.0 | 2014-12-22 11:42
* Feature: Twig template engine. Enable Twig in your config ini to use .twig templates instead
          of normal .php for your views.
    
##2.2.1 | 2014-12-18 15:19
* Fixed: Socket for PDO set, needed for panoramix.mostwantit.nl
* Fixed: User PDO::bindValua instead of PDO::bindParam, width PDO::PARAM_[type].

##2.2.0 | 2014-10-09 11:27
* Feature: New module Login. Enable this to automatically call for a logged in user. Otherwise 
          show login page.
* Fixed:   Double overriding CSRF keys.
* Fixed:   Empty Messages on Message::getAll();

##2.1.8 | 2014-07-01 22:35
* Feature: ModelObject::setObject(), find the first object and set it to values.

##2.1.7 | 2014-05-02 21:20
* Feature: escape(), escape a string for HTML output. @package sanitize.
    
##2.1.6 | 2014-03-04 13:53
* Fixed: Query::where() now handles param binding in combination with functions.

##2.1.5 | 2014-02-27 16:08
* Fixed:   ASSET_DIR now includes complete dir.
* Feature: objectToArray($objects, $key), create a simple array out of a set of objects and use 
          $key for value(s)

##2.1.4 | 2014-02-15 13:23
* Fixed: Query aliasses.

##2.1.3 | 2014-02-13 14:38
* Fixed: Query param binding, prevent duplicate :column.
* Fixed: Add index to view when controller exists, but view doesn't.

##2.1.2 | 2014-01-24 09:14
* Feature: System functions can now be overriden.
* Feature: Module Cross Site Request Forgery (CSRF) protection.

##2.1.1 | 2013-12-21 13:58
* Fixed: Model::$tableName wil not be overridden if set in ModelObject.

##2.1.0 | 2013-12-19 13:29
* Feature: Cache can now cache to database.

##2.0.0 | 2013-12-13 16:10
* Feature: Data/ActiveRecord for object query chaining.
* Removed: ActiveRecord extension for Query.
* Feature: Database now runs PDO with prepared queries, so you need to bind your params.
* Feature: randStr() now uses regexp instead of array for exclusion. Also you can in/exclude special
          characters like ?, ~, #, @, etc. by setting the 3rd parameter to false (default=true).
* Feature: isEmailAddress() now uses filter_var() instead of regexp.
* Feature: hashStr() now uses php own hash() function, algorithm can be set in config (hash_algo).
* Fixed:   Rewrite now actually works.
* Feature: Template::getAssignedVars(), return all assigned vars.
* Feature: Assigned vars are now available in layout templates.
* Feature: New setting: load_template_engine, when set to false no template engine will be loaded.
* Feature: Config::getSetting(), added parameter for returning a default value when no error is 
          set to false.
* Fixed:   Use spl_autoload_register instead of __autoload.
* Fixed:   ROOT_PATH now correctly represents the root when core is outside the documentroot.
* Feature: str_lreplace(), only replace last occurance in string.
* Fixed:   Minor bugfixing

##1.0.10 | 2013-11-06 13:18
* Feature: ActiveRecord extension for Query.
* Feature: Crypt module, for encrypting an decrypting.

##1.0.9 | 2013-11-01 12:47
* Fixed:   $template is now available in index.php, replacing the $_ASSIGN.
* Feature: Model::create(), return created object(s) or false on failure (instead of an empty object).
* Feature: Template::getUriVars(), return all uri vars.
* Feature: Template::getUrl(), passing no|empty parameter now returns the complete url array.

##1.0.8 | 2013-07-11 11:37
* Fixed:   Template uri vars are now sanitized.
* Fixed:   Model::deleteObject() now uses correct Database instance.
* Feature: Template::$view can be rewritten when loaded from url. Set [REWRITE] in config .ini
* Feature: Model::__get reverse search for objects.
* Feature: Query::group(), add GROUP BY to query.
* Feature: Template::loadView() now accepts $layout as second parameter. Set it to false to disable
          loading a layout.

##1.0.7 | 2013-06-14 15:09
* Feature: ModelObject::get() now checks whether an empty date is given, if so return null.
          Disable this by setting check_empty_date to false in the config (empty date will
          be returned).

##1.0.6 | 2013-05-24 13:38
* Feature: Possibility for override core functions. Currently only redirect() is supported.
* Fixed:   ModelObject::get(), also format date is field is Date.

##1.0.5
* Fixed:   Passing a query to Model::__construct() now uses the correct table alias.
* Feature: Query::where() now takes an array. It will add a where statement for every key = value.
* Feature: Query::join(). Add joins for a query, set table, on and type.

##1.0.4
* Feature: Model::createObjects(). Create objects from a query string or an array with multiple
          value sets

##1.0.3 | 2013-03-01 13:14
* Feature: Query chaining. Use Query::chain() to start chaining instead of new Query()

##1.0.2 | 2013-02-26 12:29
* Fixed:   Routing, it works now
* Feature: Defined version number in load.php (CW_VERSION)
* Feature: Image module, for rescaling and cropping images

##1.0.1 | 2013-02-20 17:16
* Fixed:   Sanitize filterInput double defintion
* Fixed:   Template 404 view
* Feature: Date module, for formatting date
* Feature: Locale can be set in config .ini as locale = 'en_EN'
* Feature: Config::getSetting() can now take a second (boolean) argument, which defines 
          whether to show an error or not when a setting does not exist