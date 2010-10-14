<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008, 2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Task-Class for symfony command-line task for the generation of AMF-Service
 * base class from database
 *
 * @author Stephane Bachelier (http://blog.0x89b.org)
 * @copyright Stephane Bachelier
 * @license MIT
 * @version SVN: $Id $
 */

class sfAmfPluginBuildServiceTask extends sfAmfPluginGeneratorTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', '-c', sfCommandOption::PARAMETER_REQUIRED, 'The connection name', sfConfig::get('sf_orm')),
      new sfCommandOption('service_dir', '-s', sfCommandOption::PARAMETER_REQUIRED, 'The service dir name', 'services'),
      new sfCommandOption('package', '-p', sfCommandOption::PARAMETER_REQUIRED, 'Package name (i.e. org.symfony.services)'),
      new sfCommandOption('base_package', '-b', sfCommandOption::PARAMETER_REQUIRED, 'Package name for based classes (i.e. org.symfony.service.base)', 'base'),
      new sfCommandOption('vo_package', '-v', sfCommandOption::PARAMETER_REQUIRED, 'Package name for valueobjects classes (i.e. org.symfony.service.vo)', 'vo'),
    ));

    $this->namespace        = 'amf';
    $this->name             = 'build-services';
    $this->briefDescription = 'Generate base services based on schema';
    $this->detailedDescription = <<<EOF
The amf:build-services task generates both a service class to be filled and a base service class that provides basic CRUD operations for each table in database.
A value object class is also generated

Call it with:

  [php symfony amf:build-services|INFO]
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array()) 
  {
    // generate base services
    $this->generate(
      'Service', 
      $options['connection'], 
      array(
        'connection'        => $options['connection'],
        'service_dir'       => $options['service_dir'],
        'package'           => $options['package'],
        'base_package'      => $options['base_package'],
        'vo_package'        => $options['vo_package'],
    ));

    $this->reloadAutoload();
  }

}
    
