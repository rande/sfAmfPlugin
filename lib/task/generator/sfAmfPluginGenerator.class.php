<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008, 2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generator-Class for the generation of AMF-Service class based on Doctrine 
 *
 * @author Stephane Bachelier (http://blog.0x89b.org)
 * @copyright Stephane Bachelier
 * @license MIT
 * @version SVN: $Id $
 */

abstract class sfAmfPluginGenerator extends sfGenerator
{
  protected $package = null;
  protected $basePackage = null;
  protected $voPackage = null;
  protected $serviceDirname = null;
  protected $connection = null;
  protected $serviceParent = null;

  // for logging
  private $dispatcher = null;
  private $formatter = null;
  private $logSection = 'amf';

  /**
   *
   */ 
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->dispatcher = new sfEventDispatcher();
    $this->formatter = new sfFormatter();
  }

  private function initializeLogSection(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter  = $formatter;
  }

  /**
   *
   */ 
  public function logSection($message, $size = null, $style = 'INFO')
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($this->logSection, $message, $size, $style))));
  }

  /**
   *
   */ 
  public function generate($params = array())
  {
    $this->initializeLogSection($params['dispatcher'], $params['formatter']);

    $this->setPackage($params['package']);
    $this->setBasePackage($params['base_package']);
    $this->setVoPackage($params['vo_package']);
    $this->setServiceDirname($params['service_dir']);
    $this->setConnection($params['connection']);

    // verify and generate
    $this->checkServiceDirectories();
    $this->loadModels();
    $this->generateAll();
  }

  abstract protected function setConnection($connection);
  abstract protected function loadModels();

  abstract protected function generateAll();

  /**
   *
   */ 
  protected function replaceTokens($file)
  {
    $properties = parse_ini_file(sfConfig::get('sf_config_dir').'/properties.ini', true);
    $constants = array(
      'PROJECT_NAME'  => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'AUTHOR_NAME'   => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
      'PACKAGE_NAME'  => !is_null($this->package) ? $this->package : 'package name', 
    );  

    if (!is_readable($file))
    {
      throw new sfCommandException("Failed to replace tokens as file is not accessible.");
    }

    // customize service file
    $sfFilesystem = new sfFilesystem();
    $sfFilesystem->replaceTokens($file, '##', '##', $constants);

  }

  /**
   *
   */ 
  protected function setPackage($name)
  {
    $this->package = $name;
  }

  /**
   *
   */ 
  protected function getPackage()
  {
    return $this->package;
  }

  /**
   *
   */ 
  protected function setBasePackage($name)
  {
    $this->basePackage = $name;
  }

  /**
   *
   */ 
  protected function getBasePackage()
  {
    return $this->basePackage;
  }

  protected function getFullBasePackage()
  {
    return $this->getPackage() . '.' . $this->getBasePackage();
  }

  /**
   *
   */ 
  protected function setVoPackage($name)
  {
    $this->voPackage = $name;
  }

  /**
   *
   */ 
  protected function getVoPackage()
  {
    return $this->voPackage;
  }

  protected function getFullVoPackage()
  {
    return $this->getPackage() . '.' . $this->getVoPackage();
  }

  /**
   *
   */
  protected function getPackageDirectory($package = null)
  {
    $package = is_null($package) ? $this->package : $package; 

    if (is_null($package))
      return '';

    return str_replace('.', '/', $package);
  }

  /**
   *
   */ 
  protected function setServiceDirname($name)
  {
    $this->serviceDirname = $name;
  }

  /**
   *
   */ 
  protected function getServiceDirname()
  {
    return !is_null($this->serviceDirname) ? $this->serviceDirname : 'services';
  }

  /**
   *
   */
  protected function getServiceDirectory($package = null)
  {
    return sfConfig::get('sf_lib_dir') . '/' . 
                    $this->getServiceDirname() . '/' . 
                    $this->getPackageDirectory($package) . '/';
  }

  /**
   *
   */ 
  protected function getAbsoluteFileName($name, $package = null)
  {
    return $this->getServiceDirectory($package) . $name . '.class.php';
  }

  /**
   *
   */ 
  protected function checkServiceDirectories()
  {
    if (!is_dir($this->getServiceDirectory()))
    {
      mkdir($this->getServiceDirectory(), 0777, true); 
    }
  }

  protected function writeFile($file, $template)
  {
    if (!file_exists($file))
    {
      if (!is_dir($directory = dirname($file)))
      {
        mkdir($directory, 0777, true);
      }
    }

    file_put_contents($file, $template);
    $this->replaceTokens($file);

    $this->logSection(sprintf("Added %s", basename($file)));
  }

  protected function generateServiceFilesForModel($model)
  {
      $serviceFileName = $model . 'Service';
      $baseServiceFileName = 'Base' . $serviceFileName;

      // services
      $baseServiceFile = $this->getAbsoluteFileName($baseServiceFileName, $this->getFullBasePackage());

      $this->writeFile(
        $baseServiceFile,
        $this->evalTemplate('sfAmfBaseServiceTemplate.php')
      );

      // services
      $serviceFile = $this->getAbsoluteFileName($serviceFileName);

      if (!file_exists($serviceFile))
      {
        $this->serviceParent = $baseServiceFileName;

        $this->writeFile(
          $serviceFile,
          $this->evalTemplate('sfAmfServiceTemplate.php')
        );
      }

      // valueobjects
      $voFile = $this->getAbsoluteFileName($model . 'ValueObject', $this->getFullVoPackage());

      $this->writeFile(
        $voFile,
        $this->evalTemplate('sfAmfValueObjectTemplate.php')
      );

  }
}
