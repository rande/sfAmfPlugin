<?php
class sfAmfPluginConfiguration extends sfPluginConfiguration
{
    public function initialize()
    {
      
        set_include_path(realpath(dirname(__FILE__).'/../lib/') . PATH_SEPARATOR . get_include_path());
    }
}