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

class sfAmfPluginDoctrineServiceGenerator extends sfAmfPluginDoctrineGenerator
{
  public function initialize(sfGeneratorManager $generatorManager)
  {
    parent::initialize($generatorManager);
  }

  public function generateAll()
  {
    foreach($this->models as $model)
    {
      $this->table = Doctrine_Core::getTable($model);
      $this->generateServiceFilesForModel($this->table->getClassnameToReturn());
    }
  }

}
