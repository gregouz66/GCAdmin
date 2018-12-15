<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
// READ ENTITY
use Doctrine\Common\Annotations\AnnotationReader;

class TestController extends AbstractController
{
    /**
     * @Route("/test", name="test")
     */
    public function index()
    {
        $docReader = new AnnotationReader();
        $entity = new User();
        $reflect = new  \ReflectionClass($entity);
        // $fields is an array whihch contain the entity name as the array key and the value as the array value
        // foreach ($fields as $key => $val)
        // {
            $key = "roles";
            if (!$reflect->hasProperty($key)) {
                dump('the entity does not have a such property');
            }
            $docInfos = $docReader->getPropertyAnnotations($reflect->getProperty($key));
            dump($docInfos[0]->type);
        // }

        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }
}
