<?php

namespace AppBundle\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * Class ApiBaseController
 * @package AppBundke\Controller
 *
 *
 * Base Controller for App Bundle
 */
class ApiBaseController extends Controller
{

    protected $sTranslator = 'translator';

    // returns an array of request's params
    protected function getQueryParams(ParamFetcher $paramFetcher)
    {
        $aOptions = $paramFetcher->getParams();
        $aResult = array();

        foreach ($aOptions as $option){
            $aResult[$option->getName()] = $paramFetcher->get($option->getName());
        }
        return $aResult;
    }

    // translating messages
    protected function translate($psMessage)
    {
        $sMessage = $this->get('translator')->trans($psMessage);

        return $sMessage;
    }


    protected function setResponse($psCode, $psMessage = "", $paResponse = [])
    {
        return [
            'code' => $psCode,
            'message' => $psMessage,
            'response' => $paResponse
        ];
    }

}