<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008-2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * AmfPhp Gateway that is extended to fit the needs of a symfony project.
 * It uses the SabreAMF library for the low-level AMF encoding/decoding
 *
 * @example
 * <code>
 * public function executeAmf() {
 *  $this->setLayout(false);
 *
 *  $gateway = new sfAmfGateway();
 *  $response = sfContext::getInstance()->getResponse();
 *  $response->setContent($gateway->service());
 *  $response->setContentType(SabreAMF_Const::MIMETYPE);
 *
 *  return sfView::NONE;
 * }
 * </code>
 *
 * @example
 * <code>
 * public function executeAmf() {
 *  $this->setLayout(false);
 *  sfAmfGateway::getInstance()->handleRequest();
 *
 *  return sfView::NONE;
 * }
 * </code>
 *
 * @author Timo Haberkern (http://www.shift-up.de)
 * @copyright Timo Haberkern
 * @license MIT
 */
class sfAmfGateway
{

  /**
   * default constructor
   */
  public function __construct()
  {
    // setting an error handler, so PHP errors get handles as
    // exception. Otherwise you will get only error messages
    // without any meaning on flex side
    set_error_handler(array('sfAmfGateway', 'errorHandler'));
  }

  /**
   * Static factory method for creating an instance of sfAmfGateway
   */
  public static function getInstance()
  {
    return new sfAmfGateway();
  }

  /**
   * Convinance method. Setting the Response content type and content
   */
  public function handleRequest()
  {
    $response = sfContext::getInstance()->getResponse();
    $response->setContentType(SabreAMF_Const::MIMETYPE);
    $response->setContent($this->service());
  }

  /**
   * Main entry point of the Gateway.
   *
   * The function automaticly sets the web-debug-toolbar to off
   *
   * @return mixed The AMF encoded result of the service.
   */
  public function service()
  {
    sfConfig::set('sf_web_debug', false);

    ob_start();
    $server = new SabreAMF_CallbackServer();
    $server->onInvokeService = array($this, 'onDispatch');
    $server->exec();

    $result = ob_get_contents();

    //Don't use ob_end_clean() not ob_get_clean() since this will kill symfony own headers
    ob_clean();

    return $result;
  }

  /**
   * Dispatching callback method. This method is called by SambreAMF after decoding the
   * AMF Request
   *
   * @param string $service_name The name of the called service
   * @param string $method_name The name of the method of the service that is called
   * @param mixed $arguments The arguments that are sent to the method
   * @return mixed The return value
   */
  public function onDispatch($service_name, $method_name, $arguments)
  {
    $service_class_path = str_replace(".", "/", $service_name);

    $lib_dirs = $this->getProjectLibDirectories();

    $service_path = null;
    foreach ($lib_dirs as $lib_dir)
    {
      $lib_dir = $lib_dir . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR;

      if (file_exists($lib_dir . $service_class_path . '.class.php'))
      {
        $service_path = $lib_dir . $service_class_path . '.class.php';
        break;
      }
      else if (file_exists($lib_dir . $service_class_path . '.php'))
      {
        $service_path = $lib_dir . $service_class_path . '.php';
        break;
      }
    }

    if (null == $service_path)
    {
      throw new Exception('Service file for ' . $service_name . ' does not exist in any lib-folder');
    }

    require_once ($service_path);

    $serviceParts = explode(".", $service_name);
    $class_name = array_pop($serviceParts);
    if (!class_exists($class_name))
    {
      throw new Exception('Class for Service ' . $service_name . ' does not exist');
    }

    $instance = new $class_name;


    if (!is_callable(array($instance, $method_name)))
    {
      throw new Exception('Service class does not have ' . $method_name . ' method');
    }

    $result = call_user_func_array(
      array($instance, 'run'),
      array($method_name, $arguments)
    );

    return $result;
  }

  /**
   * Implementation of the PHP Callback function for the set_error_handler
   * function
   *
   * @see http://de3.php.net/set_error_handler
   *
   * @param int $errno
   * @param string $errstr
   * @param string $errfile
   * @param int $errline
   */
  public static function errorHandler($errno, $errstr, $errfile, $errline)
  {
     throw new Exception($errstr .' in file : '.$errfile.' at line : '.$errline, $errno);
  }

  /**
   *
   * @return array of ReflectionClass : Services
   */
  public function parseAllServices()
  {
    $files_paths = array();

    foreach ($this->getProjectLibDirectories() as $dir)
    {
      $files_paths = array_merge($files_paths, $this->servicesRecursiveSearch($dir . DIRECTORY_SEPARATOR . 'services'));
    }

    $reflection_classes = array();

    foreach ($files_paths as $path)
    {
      $class_name = $this->getClassName($path);
      $full_package_name = $this->getPackageName($path);
      $class = new ReflectionClass($class_name);

      // drop 'base' and possible abstract classes
      if (!$class->isAbstract())
      {
        require_once $path;
        $reflection_classes[$full_package_name] = $class;
      }
    }

    return $reflection_classes;
  }

  protected function getClassName($path)
  {
    $class_name = basename($path, ".php");
    $class_name = basename($class_name, ".class");

    return $class_name;
  }

  protected function getPackageName($path)
  {
    $result = $path;

    foreach ($this->getProjectLibDirectories() as $dir)
    {
      $result = str_replace($dir . DIRECTORY_SEPARATOR . 'services', '', $result);
    }

    if ($result[0] == DIRECTORY_SEPARATOR)
    {
      // Remove leading slashes
      $result = substr($result, 1);
    }

    $class_name = $this->getClassName($path);

    $parts = explode(DIRECTORY_SEPARATOR, $result);
    array_pop($parts);
    $package_name = implode('.', $parts) . '.' . $class_name;
    return $package_name;
  }

  protected function servicesRecursiveSearch($dir)
  {
    $files_paths = array_merge(
      glob($dir . DIRECTORY_SEPARATOR . '*Service.php'),
      glob($dir . DIRECTORY_SEPARATOR . '*Service.class.php')
    );

    $children_dirs = glob($dir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);

    foreach ($children_dirs as $child_dir)
    {
      $files_paths = array_merge($files_paths, $this->servicesRecursiveSearch($child_dir));
    }

    return $files_paths;
  }


  /**
   * Returns all relevant library directories of the current gateway module
   *
   * - The lib folder of th current module
   * - The application lib folder
   * - The project lib folder
   * - All lib folders of the installed plugins
   *
   * @return array The array with absolute paths of all lib-folders
   */
  protected function getProjectLibDirectories()
  {
    // get the application lib directories
    $lib_dirs = sfContext::getInstance()->getConfiguration()->getLibDirs(sfConfig::get('sf_app'));

    // get the cross application lib dir (i.e. apps/frontend/lib)
    $lib_dirs[] = sfConfig::get('sf_app_dir') . DIRECTORY_SEPARATOR . 'lib';

    // get the project lib dir
    $lib_dirs[] = sfConfig::get('sf_lib_dir');

    // get the plugin lib dirs
    $lib_dirs = array_merge(
      $lib_dirs,
      sfContext::getInstance()->getConfiguration()->getPluginSubPaths('/lib')
    );

    return $lib_dirs;
  }
}
