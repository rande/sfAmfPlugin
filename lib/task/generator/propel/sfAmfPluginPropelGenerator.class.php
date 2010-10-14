<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008, 2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Generator-Class for the generation of AMF-Service class based on Propel 
 *
 * @author Stephane Bachelier (http://blog.0x89b.org)
 * @copyright Stephane Bachelier
 * @license MIT
 * @version SVN: $Id $
 */

abstract class sfAmfPluginPropelGenerator extends sfAmfPluginGenerator
{
  protected $dbMap = null;

  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfAmfPropelService');
  }

  protected function setConnection($connection)
  {
    $this->dbMap = Propel::getDatabaseMap($connection);
  }

  /**
   * load map builder classes
   */ 
  protected function loadModels()
  {
    if (is_null($this->dbMap))
    {
      throw new sfCommandException('Connection is not set');
    }

    $classes = sfFinder::type('file')->name('*TableMap.php')->in($this->generatorManager->getConfiguration()->getModelDirs());

    if (count($classes) == 0)
    {
      throw new sfCommandException("Model classes are not found! Are you sure that you've run php symfony propel:build-model ? ");
    }

    foreach( $classes as $class )
    {
      $omClass = basename($class, 'TableMap.php');
      if (class_exists($omClass) && is_subclass_of($omClass, 'BaseObject'))
      {
        $tableMapClass = basename($class, '.php');
        $this->dbMap->addTableFromMapClass($tableMapClass);
      }
    }
  }
}
