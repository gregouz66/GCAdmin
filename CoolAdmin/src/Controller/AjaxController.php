<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AjaxController extends AbstractController
{
    /**
     * @Route("/admin/ajax/menu", name="ajaxMenu")
     */
    public function index(Request $request)
    {
        //IF AJAX
        if($request->isXmlHttpRequest()){
            // Menu Settings
            $classMenu = $this->getParameter('GCAdmin')['menu'];
            return new JsonResponse($classMenu);
        }

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
