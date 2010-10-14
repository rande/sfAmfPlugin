<?php
/**
 * @package:     sfAmfPluginTask
 * @author:      Stephane Bachelier (stephane.bachelier@gmail.com)
 * @license:     GPL (see http://www.gnu.org/licenses/gpl.txt)
 * @created:     2010-03-09.
 * @last change: 19-Mär-2005.
 * @version:     0.0
 * Description:
 * Usage:
 * TODO:
 * CHANGES:
 * 
 */

abstract class sfAmfPluginGeneratorTask extends sfBaseTask {
    
  /**
   *
   */ 
  protected function generatorClass($connection, $generator)
  {
    switch($connection)
    {
      case 'propel':
      case 'doctrine':
        $class = 'sfAmfPlugin' . ucfirst($connection) . $generator . 'Generator';

        if (!class_exists($class))
        {
          throw new sfCommandException(sprintf("[%s] generator is not found", $class));
        }

        return $class;

      default:
        throw new sfCommandException("Connection is not supported. (Only propel or doctrine)");  
    }
  }

  protected function generate($type, $connection, $params)
  {
    $baseOptions = array(
      'dispatcher'        => $this->dispatcher,
      'formatter'         => $this->formatter,
    );

    $generatorManager = new sfGeneratorManager($this->configuration);
    $generatorManager->generate($this->generatorClass($connection, $type), array_merge($baseOptions, $params));
  }
}
