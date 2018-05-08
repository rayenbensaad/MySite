<?php
/**
 * Created by PhpStorm.
 * User: safa
 * Date: 05/04/18
 * Time: 13:38
 */

namespace AppBundle\Controller;
use AppBundle\Entity\Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
//use AppBundle\Entity\AuthToken;

class ValidCodeController extends ApiBaseController
{
    /**
     * Client Validation
     * This call takes a code, stores it into database .
     * @Rest\POST("/user/valid-code")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=400,description="Missing  parameter",)
     * @SWG\Response(response=401,description=" parameter should not be blank",)
     * @SWG\Response(response=402,description="Wrong code",)
     *
     * @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *                  @SWG\Property(
     *                     property="code",
     *                     type="string",
     *                  ),
     *                  @SWG\Property(
     *                     property="mail",
     *                     type="string",
     *                  ),
     *          )
     *     ),
     * @SWG\Tag(name="Client")
     *
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View()
     */

    public function ValidCodeAction(Request $request)
    {
        $mail=$request->query->get('mail');

        $em = $this->get('doctrine.orm.entity_manager');
        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy([
                'mail' => $mail
            ]);

        $oUser->setValid(1);
        $em->persist($oUser);
        $em->flush();

//        return $this->redirect('http://localhost:4200/login');


    }
}
