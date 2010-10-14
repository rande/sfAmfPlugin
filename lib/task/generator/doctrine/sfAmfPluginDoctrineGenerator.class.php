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

abstract class sfAmfPluginDoctrineGenerator extends sfAmfPluginGenerator
{
  protected $models;

  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);

    $this->setGeneratorClass('sfAmfDoctrineService');
  }

  protected function setConnection($connection)
  {
    $configuration = $this->generatorManager->getConfiguration();
    $databaseManager = new sfDatabaseManager($configuration); 
    $databaseManager->initialize($configuration); 

  }

  protected function loadModels()
  {

    // todo : fix this part to load model from the plugins ...
    $basePath = $this->generatorManager->getConfiguration()->getRootDir();
    $schemas = sfFinder::type('file')->name('*.yml')->in($basePath . '/config/doctrine');

    foreach ($schemas as $schema)
    {
      $data = Doctrine_Parser::load($schema, 'yml');

      $models = array();

      foreach ($data as $model => $definition)
      {
        try
        {
          Doctrine::getTable($model)->getTableName();
          $models[] = $model;
        }
        catch(Exception $e)
        {

        }
      }
      $this->models = $models;
    }
  }
}
