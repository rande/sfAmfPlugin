<?php

/**
 * amfBrowser actions.
 *
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class amfBrowserActions extends sfActions {

    public function preExecute() {
        //VoMapper::setEnabledVos(true);
    }

    /**
     * Executes amf action
     *
     * @param sfWebRequest $request A request object
     */
    public function executeAmf(sfWebRequest $request) {
        $this->setLayout(false);

        $gateway = new sfAmfGateway();
        $response = sfContext::GetInstance()->getResponse();
        $response->setContent($gateway->service());
        return sfView::NONE;
    }

    /**
     * Executes index action
     *
     * @param sfWebRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        $this->setLayout(false);

        $gateway = new sfAmfGateway();
        $this->services_reflections = $gateway->parseAllServices();

        $this->resp = null;
        $this->errors = array();
        $this->method_name = null;
        $this->service_name = null;
        $this->service_method_name = null;

        if ($request->getParameter('method')) {
            $this->service_method_name = $request->getParameter('method');

            list($this->package_name, $this->method_name) = explode('::', $this->service_method_name);

            $this->service_name = $this->services_reflections[$this->package_name]->getName();

            $this->method_reflection = new ReflectionMethod($this->services_reflections[$this->package_name]->getName(), $this->method_name);

            if ($request->isMethod('post')) {
                $this->arguments = $request->getPostParameter('param', array());

                foreach ($this->arguments as &$arg) {
                    $arg = trim($arg);

                    //-- JSON object
                    if (substr($arg, 0, 1) == '[' or substr($arg, 0, 1) == '{') {
                        $arg = json_decode($arg);
                        if (is_null($arg))
                            $this->errors[] = 'Bad-formatted JSON!';
                    }
                    //-- As-Is string
                    elseif (substr($arg, 0, 1) == '"' and substr($arg, -1, 1) == '"') {
                        $arg = substr($arg, 1, -1);
                    }
                }

                if (!$this->errors) {
                    $this->method_return_resp = $gateway->onDispatch($this->package_name, $this->method_name, $this->arguments);
                    $this->amf_return_resp = $this->_makeAmfResponseFromMethodReturn($this->method_return_resp);
                }
                else {
                    $this->method_return_resp = null;
                    $this->amf_return_resp = null;
                }
            }
        }
    }

    /**
     * Real AMF functioning simulation
     *
     * @param <type> $method_return
     * @return <type>
     */
    protected function _makeAmfResponseFromMethodReturn($method_return) {

        $response = new SabreAMF_AMF3_AcknowledgeMessage();
        $response->body = $method_return;

        $amfResponse = new SabreAMF_Message();
        $amfResponse->addBody(array('target'=>'/onResult','response'=>'','data'=>$response));

        $amfOutputStream = new SabreAMF_OutputStream();
        $amfResponse->serialize($amfOutputStream);

        $amfInputStream = new SabreAMF_InputStream($amfOutputStream->getRawData());

        $amfResponse2 = new SabreAMF_Message();
        $amfResponse2->deserialize($amfInputStream);

        $bodies = $amfResponse2->getBodies();
        //var_dump($bodies);

        return $bodies[0]['data']->body;
    }


}
