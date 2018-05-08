<?php



namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\AuthToken;
//use AppBundle\Services\CodeGenerator;
//use AppBundle\Utils\AppTools;
use Swift_Message;
use Symfony\Bundle\MonologBundle\SwiftMailer;

class ClientController extends ApiBaseController
{
    /**
     * User Inscription
     *
     * This call takes a phone number, stores it into database and send an SMS CODE to this number.
     *
     * @Rest\Post("/user/inscription")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=400,description="Missing  parameter",)
     * @SWG\Response(response=401,description=" parameter should not be blank",)
     * @SWG\Response(response=402,description="Your Mail is not in the correct format",)
     * @SWG\Response(response=403,description="Your Mail already exists",)
     *
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *           @SWG\Property(
     *              property="username",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="mail",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="type",
     *              type="string",
     *          ),
     *          @SWG\Property(
     *              property="password",
     *              type="string",
     *          ),
     *
     *    )
     * ),
     *
     * @SWG\Tag(name="Client")
     *
     * @param Request $request
    //* @param CodeGenerator $codeGenerator
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View()
     */
    public function inscriptionUserAction(Request $request)
    {

        if (!$request->request->has('username'))
            return $this->setResponse(400, ' Missing Username parameter');

        if (!$request->request->get('username'))
            return $this->setResponse(401, ' Username parameter should not be blank');


        if (!$request->request->has('mail'))
            return $this->setResponse(400, 'Missing mail parameter');

        if (!$request->request->get('mail'))
            return $this->setResponse(401, 'mail parameter should not be blank');


        if (!$request->request->has('type'))
            return $this->setResponse(400, 'Missing type parameter');

        if (!$request->request->get('type'))
            return $this->setResponse(401, ' Type parameter should not be blank');


        if (!$request->request->has('password'))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get(('password')))
            return $this->setResponse(401, 'Password parameter should not be blank');
        $aOptions = $request->request->all();


        // set infos
        $em = $this->getDoctrine()->getManager();
        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy(['mail' => $aOptions['mail']]);


        if ($oUser)
            return $this->setResponse(403, 'Email already exists');

        else {
            $oUser = new Client();

            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($oUser, $aOptions['password']);

            $token = uniqid();
            $oUser->setUsername($aOptions['username'])
                ->setMail($aOptions['mail'])
                ->setType($aOptions['type'])
                ->setPassword($encoded)
                ->setCode($token)
                ->setValid(0);

            $em->persist($oUser);
            $em->flush();
            $mailTitle = "Inscription User";
            /*------------------- send mail --------*/
            $this->SendMailAction($aOptions['mail'], $token, $aOptions['mail'], $mailTitle, 'emails/Confirmation.html.twig');

        }
        return $this->setResponse(200, 'User Created Successfully');


    }

    /**
     * Forget password
     * @Rest\Post("/client/forget-password")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=400,description="Missing  parameter",)
     * @SWG\Response(response=401,description=" parameter should not be blank",)
     * @SWG\Response(response=402,description="Your Mail is not in the correct format",)
     * @SWG\Response(response=403,description="Your Mail already exists",)
     *
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="mail",
     *              type="string",
     *          ),
     *     )
     * ),
     *
     * @SWG\Tag(name="Client")
     *
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View()
     */
    public function forgetPasswordUserAction(Request $request)
    {
        if (!$request->request->has('mail'))
            return $this->setResponse(400, 'Missing mail parameter');
        if (!$request->request->get('mail'))
            return $this->setResponse(401, ' Mail parameter should not be blank');
        $aOptions = $request->request->all();


        $em = $this->getDoctrine()->getManager();
        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy(['mail' => $aOptions['mail']]);

        if ($oUser == null) {
            return $this->setResponse(403, 'Email not exists try again');
        } else {
            if ($request->request->has('newpassword') && $request->request->has('newpasswordconf')) {
                if ($request->request->get('newpassword') == $request->request->get('newpasswordconf')) {
                    $encoder = $this->get('security.password_encoder');
                    $encoded = $encoder->encodePassword($oUser, $request->request->get('newpassword'));
                    $oUser->setPassword($encoded);
                    $em->flush();
                    return $this->setResponse(200, 'success');
                } else {
                    return $this->setResponse(405, 'please try to verify your new password');
                }

            } else {
                $em->merge($oUser);
                $em->flush();
                $mailTitle = 'Forget Password';
                /*------------------- send mail --------*/
                $this->SendMailAction($aOptions['mail'], $aOptions['mail'], $mailTitle, 'emails/forgetpassword.html.twig');

            }
        }
        return $this->setResponse(200, 'Success');
    }


    /**
     * Update password
     * @Rest\Put("/user/update-password")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=400,description="Missing  parameter",)
     * @SWG\Response(response=401,description=" parameter should not be blank",)
     * @SWG\Response(response=402,description="Your Mail is not in the correct format",)
     * @SWG\Response(response=403,description="Your Mail already exists",)
     *
     *
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(
     *              property="mail",
     *              type="string",
     *          ),
     *     )
     * ),
     *
     * @SWG\Tag(name="Client")
     *
     * @param Request $request
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View()
     */
    public function UpdatePasswordUserAction(Request $request)
    {
        if (!$request->request->has('password'))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get(('password')))
            return $this->setResponse(401, 'Password parameter should not be blank');

        if (!$request->request->has('newpassword'))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get(('newpassword')))
            return $this->setResponse(401, 'Password parameter should not be blank');

        if (!$request->request->has('newpasswordconfirmation'))
            return $this->setResponse(400, 'Missing password confirmation parameter');

        if (!$request->request->get(('newpasswordconfirmation')))
            return $this->setResponse(401, 'Password Confirmation parameter should not be blank');

        $aOptions = $request->request->all();

        $em = $this->getDoctrine()->getManager();
        //------ il faut crypter password login ------
        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy([
                'mail' => $request->request->get(('mail')),

            ]);
        //$encoder = $this->get('security.password_encoder');
        //$encoded = $encoder->encodePassword($oUser,$aOptions['password']);
        dump($oUser->getPassword());
        if ($request->request->has('newpassword') && $request->request->has('newpasswordconfirmation')) {
            if ($request->request->get('newpassword') == $request->request->get('newpasswordconfirmation')) {
                $encoder = $this->get('security.password_encoder');
                $encoded = $encoder->encodePassword($oUser, $request->request->get('newpassword'));
                $oUser->setPassword($encoded);
                $em->flush();
                return $this->setResponse(200, 'success');
            } else {
                return $this->setResponse(405, 'please try to verify your new password');
            }

        }

    }

    private function SendMailAction($mailReceiver, $data, $code, $mailTitle, $template, $time = null)
    {

        $message = Swift_Message::newInstance()
            ->setSubject($mailTitle)
            ->setFrom($this->getParameter('mailer_user'))
            ->setTo($mailReceiver)
            ->setBody($this->renderView($template, array('code' => $data, 'mail' => $code, 'date' => time())), 'text/html');

        $this->get('mailer')->send($message);
    }


    /**
     * User Authentication
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
        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy([
                'mail' => $smail,

            ]);

        if (!$oUser) {

            return $this->setResponse(402, 'Wrong mail');
        }
        // test password

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($oUser, $request->request->get('password'));

        if (!$isPasswordValid)
            return $this->setResponse(403, 'Invalid password');

        if (!$oUser->getValid() == 1) {
            return $this->setResponse(200, 'Compte invalid');
        }


        $Client = $oUser;
        // creating new token for the User
        $authToken = new AuthToken();
        $authToken->setValue(base64_encode(random_bytes(50)));
        $authToken->setCreatedAt(new \DateTime('now'));
        $authToken->setUser($Client);

        $em->persist($authToken);
        $em->flush();

        return $this->setResponse(200, 'Success', $authToken);
        //return $authToken;
    }


    /**
     * Create Client
     *
     * @Rest\Post("/client/create-client")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=404,description="No client",)
     * @SWG\Response(response=400,description="Missing parameter",)
     * @SWG\Response(response=401,description="parameter should not be blank",)
     *
     *
     * @SWG\Tag(name="Client")
     *
     *
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View(serializerGroups={"client"})
     */
    public function CreateClientAction(Request $request)
    {
        if (!$request->request->has(('username')))
            return $this->setResponse(400, ' Missing username parameter');

        if (!$request->request->get(('username')))
            return $this->setResponse(401, ' Username parameter should not be blank');


        if (!$request->request->has(('mail')))
            return $this->setResponse(400, 'Missing mail parameter');

        if (!$request->request->get(('mail')))
            return $this->setResponse(401, 'Mail parameter should not be blank');



        if (!$request->request->has(('type')))
            return $this->setResponse(400, 'Missing type parameter');

        if (!$request->request->get(('type')))
            return $this->setResponse(401, ' Type parameter should not be blank');

        if (!$request->request->has(('password')))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get(('password')))
            return $this->setResponse(401, 'Password parameter should not be blank');


        $aOptions = $request->request->all();

        $oClientt = new Client();

        // set infos

        $oClientt->setUsername($aOptions['username'])
            ->setMail($aOptions['mail'])
            ->setType($aOptions['type'])
            ->setPassword($aOptions['password']);



        $em = $this->getDoctrine()->getManager();
//        if ($oClientt)
//            return $this->setResponse(402, 'Client already exists');

        // persist data

        $em->persist($oClientt);
        $em->flush();

        return $this->setResponse(200, 'Success');


    }

    /**
     * Get All Client
     *
     * This call retrieves all clients
     *
     * @Rest\Get("/client/get-all-clients")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=404,description="No client",)
     *
     * @SWG\Tag(name="Client")
     *
     *
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View(serializerGroups={"client"})
     */

    public function getAllClientAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $aClients = $em->getRepository('AppBundle:Client')->findAll();

        if (!$aClients)

            return $this->setResponse(400, 'No client found');

        //return $this->setResponse(200, 'Success', ['clients' => $aClients]);

        return $this->setResponse(200, 'Success',  $aClients);
    }


    /**
     * Get Clients
     *
     * This call retrieves  clients
     *
     * @Rest\Get("/client/get-clients")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=404,description="No client",)
     *
     * @SWG\Tag(name="Clients")
     *
     *
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View(serializerGroups={"client"})
     */

    public function getClientsAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $aClients = $em->getRepository('AppBundle:Client')->findAll();

        if (!$aClients)

            return $this->setResponse(400, 'No client found');

        //return $this->setResponse(200, 'Success', ['clients' => $aClients]);

        return $this->setResponse(200, 'Success',  $aClients);
    }


    /**
     * Get Client
     *
     * This call retrieves  client
     *
     * @Rest\Get("/client/get-client/use/{id}")
     *
     * @SWG\Response(response=200,description="Success",)
     * @SWG\Response(response=404,description="No client",)
     *
     * @SWG\Tag(name="Client")
     *
     * @return array|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|static
     * @Rest\View(serializerGroups={"login-admin"})
     */

    public function getClientAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $aClients = $em->getRepository('AppBundle:Admin')->find($id);

        if (!$aClients) {

            return $this->setResponse(400, 'No client found');

        }
        return $this->setResponse(200, 'Success', ['clients' => $aClients]);

    }



    /**
     * User Validation
     * This call takes a code, stores it into database .
     * @Rest\Post("/client/valid-code")
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

        var_dump($request);
        die();

        if (!$request->request->has('mail'))
            return $this->setResponse(400, 'Missing mail parameter');

        if (!$request->request->get('mail'))
            return $this->setResponse(401, ' Mail parameter should not be blank');


        if (!$request->request->has('password'))
            return $this->setResponse(400, 'Missing password parameter');

        if (!$request->request->get('password'))
            return $this->setResponse(401, 'Password parameter should not be blank');


        if(!$request->request->has('code'))
            return $this->setResponse(400, 'Missing code parameter');

        if(!$request->request->get('code'))
            return $this->setResponse(401, 'Code parameter should not be blank');


        $smail=$request->request->get('mail');
        $spassword=$request->request->get('password');
        $sCode=$request->request->get('code');
        var_dump($smail);
        var_dump($spassword);
        var_dump($sCode);

        $em = $this->get('doctrine.orm.entity_manager');

        $oUser = $em->getRepository('AppBundle:Client')
            ->findOneBy([
                'mail' => $smail,
                'password' =>md5($spassword),
                'code' => $sCode,
            ]);

        if (!$oUser)
        {
            return $this->setResponse(402, 'Wrong parameter');
        }

        else
        {
            $oUser->setValid(1);

        }


        //return $this->setResponse(200, 'Success');
        //return $this->redirect('http://localhost:4200/login');
        return new RedirectResponse('http://localhost:4200/login');
    }


}