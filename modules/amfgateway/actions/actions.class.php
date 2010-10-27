<?php

/**
 * sfAmfPlugin gateway actions
 *
 * @package    sfAmfPlugin
 * @author     Timo Haberkern
 * @version    SVN: $Id: actions.class.php 20807 2009-08-05 15:50:39Z thaberkern $
 */
class amfgatewayActions extends sfActions
{
  /**
   *  Default amf gateway module
   */
  public function executeService()
  {
    $this->setLayout(false);

    $gateway = new sfAmfGateway(
      $this->context->getConfiguration()->getEventDispatcher(),
      $this->getResponse()
    );

    $gateway->handleRequest();

    return sfView::NONE;
  }
}
