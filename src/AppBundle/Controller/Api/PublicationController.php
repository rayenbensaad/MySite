<?php
namespace AppBundle\Controller\Api;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
class PublicationController extends Controller
{
    /**
     * @Route("/api/posts/{id}",name="show_post")
     * @Method({"GET"})
     */
    public function showPublication($id)
    {
        $publication=$this->getDoctrine()->getRepository('AppBundle:Publication')->find($id);
        if (empty($publication)){
            $response=array(
                'code'=>1,
                'message'=>'publication not found',
                'error'=>null,
                'result'=>null
            );
            return new JsonResponse($response, Response::HTTP_NOT_FOUND);
        }
        $data=$this->get('jms_serializer')->serialize($publication,'json');
        $response=array(
            'code'=>0,
            'message'=>'success',
            'errors'=>null,
            'result'=>json_decode($data)
        );
        return new JsonResponse($response,200);
    }




}