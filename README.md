sfAmfPlugin
=========================

Overview
--------

The sfAmfPlugin provides you with all you need to write symfony backends for Flex clients.
The communication is done via Adobes AMF protocol. On serverside the [SabreAMF](http://code.google.com/p/sabreamf) library is used
to encode/decode the AMF streams. For parsing the annotations the [Addendum](http://code.google.com/p/addendum/) library use used

If you look for a tutorial you can find one here: http://www.symfony-zone.com/wordpress/2009/04/15/helloworld-example-with-flex-and-symfony/

Installation
------------

The easiest way to install the plugin is to use the plugin:install command of symfony.

	$ php symfony plugin:install sfAmfPlugin


If you preffer you can use the current development version from the subversion repository 
(http://svn.symfony-project.com/plugins/sfAmfPlugin) instead. Copy the checked out version to the plugins folder of 
your project and execute the command 

	$ symfony cc



That's it 

Usage
-----

AMF-Client Requests are try to execute Services on the backend. Therefore you have to create a RemoteObject in 
Flex that has a Symfony-URL (i.e http://flextest/Test/amf).

All your services you can call will need to be saved the Service class in any
lib-folder of your project. Just create a service folder under lib and put your class
there.

Examples:
sf_root_dir/lib/services
sf_root_dir/apps/appname/libs/services
sf_root_dir/apps/appname/modules/modulname/libs/services
sf_root_dir/plugins/pluginname/libs/services

In the lib/service you can add as many subfolders as you need/want. Services on Flex-Side can use package names like
org.company.project.Servicename. You can use this with symfony too. All you need to do is to save this 
Service under lib/services/org/company/project/Servicename.php. That's it!

A service can look like this:
    [php]
      class TestService extends sfAmfService {
          public function getValues() {
              return array('value1', 'value2', 'value3');
          }
      }

Please keep in mind that your service needs to extend the sfAmfService class. By default every public function
is accessable via a AMF-Request (you can change this with annotations, see below).

Instead of creating the Services by hand, you can use the amf:create-service commandline task.

	$ symfony amf:create-service [--package=...] service_name


Sample 1:

	$ symfony amf:create-service User

will create the file UserService.class.php in the folder lib/services of your project

Sample 2:

	$ symfony amf:create-service --package=de.shiftup.projectname User

will create the file UserService.class.php in the folder lib/services/de/shiftup/projectname of your project

But how is this service called from client side? We need an URL to do so. For symfony this means you need a 
module and an action. The following listing shows you how an AMF action should look like:

The preferred way is to use the plugin amf gateway module. For that just enable the module in the settings.yml 
of your application:

    [yaml]
      enabled_modules:  [default, amfgateway]

In this case you use the following URL for your Flex services calls:
  
  http://host/amfgateway/service

Alternativly you can create your own module and action:

    [php]
      public function executeAmf() {
          $this->setLayout(false);
          sfAmfGateway::getInstance()->handleRequest();
          return sfView::NONE;
      }

And last but not least, this is possible too:

    [php]
      public function executeAmf() {
          $this->setLayout(false);

          $gateway = new sfAmfGateway();
          $response = sfContext::getInstance()->getResponse();
          $response->setContent($gateway->service());
          return sfView::NONE;
      }

AMF-Service Browser
-------------------

You can call a AMF-Service-Browser to test your Service-Classes. The Plugin contains a Service-Browser to do so. 
For that just enable the module in the settings.yml of your application:

    [yaml]
      enabled_modules:  [default, amfbrowser]
	  
Please keep in mind that you should activate this module only for the DEV-Environment. Otherwise you will create
a big security issue for your application. 
After you have added the browser to your enabled_modules run the following symfony commands:

    $ symfony cc
	$ symfony plugin:publish-assets sfAmfPlugin

Now you can call the Service-Browser via calling http://host/amfbrowser
	  
ORM-Support
-----------

The sfAmfPlugin supports both ORM-Layers of Symfony (Propel and Doctrine). So you are able to return 
Doctrine_Recordset, Doctrine_Collection and Propel-Objects from your service. The plugin is doing 
the conversion to AMF for you.

Sample:

    [php]
      class TestService extends sfAmfService {
          public function getValues() {
              $result = Doctrine::getTable('Menu')->findAll();
              return $result;
          }
      }
    
    
Annotations
-----------

You can control the behaviour of the Service via setting annotations in the DocBlock comments of the function. 

### AmfIgnore ###

By default every public funtion is accessable via a AMF-Request. With the `@AmfIgnore` Annotation you can set 
a public function to inaccessable. If you try to access this function you get an error message. 

    [php]
      /**
       * @AmfIgnore
       */
      public function getValues() {
        ...
      }

### AmfClassMapping ###

AMF has a nice feature called class mapping. In this case your result of the PHP will be automatically mapped 
to a ActionScript class on the Flex client side.
Cause Flex can not do this without some informations you have to define the ActionScript class before returning
the values from PHP. With the sfAmfPlugin you can use the `AmfClassMapping` annotation to define this class name. 

    [php]
      /**
       * @AmfClassMapping(name="ActionScriptClassName")
       */
      public function getValues() {
        ...
      }

### AmfReturnType ###

Besides class mapping the AMF plugin can convert your return data in some special ActionScript data types before 
sending them back to the Flex-Client

###### ArrayCollection ######

If you want to transfer array data you can use the return type ArrayCollecion. If so the data will casted in a 
ArrayCollection on Clientside automaticaly.

    [php]
      /**
       * @AmfReturnType("ArrayCollection")
       */
      public function getImage() {
          $values = array();
          $values[] = ...;
          $values[] = ...;

          return $values;
      }


###### ByteArray ######

Sometimes it can be useful to transfer byte array data (i.e. images) from PHP to Flex. Therefore you can set the 
returning data type of a service method to ByteArray. This is done via the `@AmfReturnType` annotation

    [php]
      /**
       * @AmfReturnType("ByteArray")
       */
      public function getImage() {
          $image = file_get_content('path/to/image.png');
          return $image;
      }

TODO
----

  * Annotations for authorization
  * Possibility to define project and class wide class mappings
  * Adding factory class for AMF controller
  * Task for creating VO ActionScript classes from Doctrine or Propel classes
  * Support for Doctrine nested sets
  * Caching
 
I like to hear from you! Maybe you have an idea how to enhance the plugin. Just send me an email! 


Changelog
---------
  * 1.5.2 (??)
    * Fixing smaller bugs
  * 1.5.1 (01-11-2010)
    * Fixed bug with DateTime handling of objects in Service-Browser
    * Improved Look and feel of the Service browser
    * Fixed typos in sfAdapterBase
    * Improved documentation
  * 1.5.0 (01-11-2010)
    * Added Support for Symfony 1.3 and 1.4
	* Updated to most current development version of SabreAMF
	* Added first version of an AMF service browser (thanks to Benoit Guchet)
  * 1.4.2 (08-05-2009)
    * Changed error_handler behaviour
    * Improved Doctrine Adapters, so they handle relations better
    * Updated SabreAmf to current version
    * Added default module for AMF-Gateway
  * 1.4.1 (07-01-2009)
    * Fixing typos in handleRequest function (thanks to Daniel Holmes for spotting this)
  * 1.4.0 (06-29-2009)
    * Added an error handler function. All Errors will now be delivered as an exception (thanks to raphox for this)
    * Fixing problem with associative array values
    * Updated SabreAmf to version 1.3.0
    * Added correct content type support, you should now use the handleRequest method in your actions as shown above (thanks to Daniel Holmes for mentioning this)
  * 1.3.0 (05-05-2009)
    * Fixing another bug with PHP Strict mode (thanks to Daniel Holmes for the fix)
    * Adding the possibility to store the services in all lib-folders of a project (app, module, project, plugins)
    * Fixing different bugs with AMF convertion of Doctrine objects
  * 1.2.4 (04-30-2009)
    * Fixing a bug with PHP Strict mode (thanks to Daniel Holmes for the fix)
  * 1.2.3 (04-16-2009)
    * Fixing bugs with package handling
  * 1.2.2 (04-12-2009)
    * Fixing installation problems over the plugin:install task
  * 1.2.1 (04-12-2009)
    * Fixing bugs in Documentation
    * Fixing installation problems over the plugin:install task
  * 1.2.0 (04-09-2009)
    * Added symfony 1.2 compatibility
    * Updated SabreAMF to version 1.2.203
    * Fixing problem with Service-Classes with package names
    * Added amf:create-service commandline task for creating service files
  * 1.1.0 (08-27-2008)
    * Added support for ArrayCollections
    * Added support for ByteArrays   
  * 1.0.0 (08-26-2008)
    * Added support for the annotations @AmfIgnore and @AmfClassMapping
    * Updated Documentation
  * 0.9.4 (08-25-2008)
    * Fixing bugs in package file
  * 0.9.3 (08-25-2008)
    * Adding support for Propel objects
  * 0.9.2 ((08-24-2008)
    * Fixing some smaller bugs
    * PEAR packaging for symfony installer support
  * 0.9.1 (08-21-2008)
    * Adding support for Doctrine objects
  * 0.9.0 (08-20-2008)
    * First public release

License
-------

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.