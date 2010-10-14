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

class sfAmfPluginPropelServiceGenerator extends sfAmfPluginPropelGenerator
{
  protected $basePackage = null;

  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);
  }

  protected function generateAll()
  {
    // generate files for each table
    foreach ($this->dbMap->getTables() as $tableName => $table)
    {
      $this->table = $table;
      $this->generateServiceFilesForModel($this->table->getClassname());
    }
  }

}
