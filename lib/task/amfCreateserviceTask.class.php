<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008, 2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Task-Class for symfony command-line task for the creation of an AMF-Service
 * class
 *
 * @author Timo Haberkern (http://www.shift-up.de)
 * @copyright Timo Haberkern
 * @license MIT
 * @version SVN: $Id $
 */
class amfCreateserviceTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    // // add your own arguments here
     $this->addArguments(array(
       new sfCommandArgument('service_name', sfCommandArgument::REQUIRED, 'Name of the Service (i.e. User)'),
     ));

    // // add your own options here
    $this->addOptions(array(
       new sfCommandOption('package', null, sfCommandOption::PARAMETER_REQUIRED, 'Package name (i.e. de.shiftup.services)'),
    ));

    $this->namespace        = 'amf';
    $this->name             = 'create-service';
    $this->briefDescription = 'Creates a new AMF-Service for the sfAmfPlugin';
    $this->detailedDescription = <<<EOF
The amf:create-service task generates a service class that is enabled to use 
via the AMF protocol from Flex or Flash. Ther service is created in the 
lib/services folder of the symfony project.

You can provide a package name. This package name is converted in a directory 
structure. The name of the service as the name "Service" attached.

Sample 1:
  [php symfony amf:create-service User]
  creates a class UserService in the lib/services folder

Sample 2:
  [php symfony amf:create-service --package=de.shiftup User]
  creates a class UserService in the lib/services/de.shiftup folder

Call it with:

  [php symfony amf:create-service|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array()) {
    $service = $arguments['service_name'];
    $package = $options['package'];

    // Validate the service name
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $service)) {
      throw new sfCommandException(sprintf('The service name "%s" is invalid.', $service));
    }

    // creating folder structure
    $serviceDir = sfConfig::get('sf_lib_dir').'/services/'.
                      str_replace(".", "/",$options['package']);
    if (array_key_exists('package', $options)) {
      $serviceDir .= '/';
    }

    $classFileName = $service.'Service.class.php';

    if (file_exists($serviceDir.$classFileName)) {
      throw new sfCommandException(sprintf('The service "%s" already exists in the "%s" folder.', $classFileName, $serviceDir));
    }

    $this->getFilesystem()->mkdirs($serviceDir);

    // generate service class file
    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);
    $constants = array(
      'PROJECT_NAME'  => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'SERVICE_NAME'  => $service,
      'PACKAGE_NAME'  => $package,
      'AUTHOR_NAME'   => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
    );

    if (is_readable(sfConfig::get('sf_data_dir').'/skeleton/amfservice'))
    {
      $skeletonDir = sfConfig::get('sf_data_dir').'/skeleton/amfservice';
    }
    else
    {
      $skeletonDir = dirname(__FILE__).'/skeleton/amfservice';
    }

    // create the new service file
    $this->getFilesystem()->copy($skeletonDir.'/service.php', $serviceDir.$classFileName);

    // customize service file
    $this->getFilesystem()->replaceTokens($serviceDir.$classFileName, '##', '##', $constants);

  }
}
