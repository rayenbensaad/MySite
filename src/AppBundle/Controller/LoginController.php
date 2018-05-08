<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use AppBundle\Entity\AuthToken;

/**
 * Class LoginController
 * @package AppBundle\Controller
 */
class LoginController extends ApiBaseController
{
    /**
     * Client Authentication
     *
     * This call takes a code and verify if it belongs to a user then provide an authentication token.
     *
     * @Rest\Post("/user/login")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=400,description="Missing  parameter",)
     * @SWG\Response(response=401,description=" parameter should not be blank",)
     * @SWG\Response(response=402,description="Wrong password",)
     *
     * @SWG\Parameter(
     *          name="body",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(
     *                  @SWG\Property(
     *                     property="mail",
     *                     type="string",
     *                  ),
     *                 @SWG\Property(
     *                    property="password",
     *                    type="string",
     *                 ),
     *          )
     *     ),
     * @SWG\Tag(name="Client")
     *
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View(serializerGroups={"login-user"})
     */
    public function loginAction(Request $request)
    {
        if (!$request->request->has('mail'))
            return $this->setResponse(400, 'Missing mail parameter');

        if (!$request->request->get('mail'))
            return $this->setResponse(401, 'Mail parameter should not be blank');


        if (!$request->request->has('password'))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get('password'))
            return $this->setResponse(401, 'Password parameter should not be blank');


        $smail = $request->request->get('mail');
        $sPassword = $request->request->get('password');

        $em = $this->get('doctrine.orm.entity_manager');
        $oClient = $em->getRepository('AppBundle:Client')
            ->findOneBy([
                'mail' => $smail,

            ]);

        if (!$oClient) {

            return $this->setResponse(402, 'Wrong mail');
        }
        // test password

        $encoder = $this->get('security.password_encoder');

        $isPasswordValid = $encoder->isPasswordValid($oClient,$request->request->get('password'));

        if(!$isPasswordValid)
            return $this->setResponse(403,'Invalid password');

        if (!$oClient->getValid()==1)
        {
            return $this->setResponse(400, 'Invalid compte');
        }


        $Client = $oClient;
        // creating new token for the User
        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setClient($Client);

        $em->persist($authToken);
        $em->flush();

        return $this->setResponse(200, 'Success', $authToken);

    }


}














