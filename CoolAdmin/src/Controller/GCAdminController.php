<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
// use App\GC\GithubBundle\GithubApi;

// READ ENTITY
use Doctrine\Common\Annotations\AnnotationReader;

class GCAdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(ObjectManager $manager)
    {
        // Statistics Settings
          $paramGeneral = $this->getParameter('GCAdmin');

          if(isset($paramGeneral['dashboard']['cube-statistics'])){
            $paramStatistics = $paramGeneral['dashboard']['cube-statistics'];

            $i = 0;
            foreach ($paramStatistics as $oneStat) {
                //FIND ALL
                $repository = $this->getDoctrine()->getRepository("App\Entity\\".$oneStat['entity']);
                $products = $repository->findAll();
                $paramStatistics[$i]['totalCount'] = count($products);
                // SQL
                $conn = $manager->getConnection();
                if(isset($oneStat['delayBy'])){
                    if($oneStat['delayBy'] == "month"){
                        $sql = "
                        SELECT COUNT(1) AS entries, MONTH(".$oneStat['attr-duration-sql'].") as date
                        FROM ".$oneStat['entity-sql']."
                        GROUP BY MONTH(".$oneStat['attr-duration-sql'].")
                        ";
                    } else {
                        //DAYS
                        $sql = "
                        SELECT COUNT(1) AS entries, DATE(".$oneStat['attr-duration-sql'].") as date
                        FROM ".$oneStat['entity-sql']."
                        GROUP BY DATE(".$oneStat['attr-duration-sql'].")
                        ";
                        // OLD
                        // $sql = "
                        // SELECT COUNT(1) AS entries, DATE(".$oneStat['attr-duration-sql'].") as date
                        // FROM ".$oneStat['entity-sql']."
                        // WHERE ".$oneStat['attr-duration-sql']." >= DATE_FORMAT(CURDATE(),'%Y-%m-".$dateMax."')
                        // GROUP BY DATE(".$oneStat['attr-duration-sql'].")
                        // ";
                    }
                } else {
                    //DAYS
                    $sql = "
                    SELECT COUNT(1) AS entries, DATE(".$oneStat['attr-duration-sql'].") as date
                    FROM ".$oneStat['entity-sql']."
                    GROUP BY DATE(".$oneStat['attr-duration-sql'].")
                    ";
                }
                
                if(isset($oneStat['limit'])){
                  $sql .= " LIMIT ".$oneStat['limit'];
                } else {
                  $sql .= " LIMIT 10"; //DEFAUT
                }

                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $resultStats[] = $stmt->fetchAll();

                // IF FORMAT DATE FR OR OTHER
                if(isset($oneStat['formatDate'])){
                    $r = 0;
                    foreach ($resultStats[$i] as $onechamp) {
                        $newDate = date($oneStat['formatDate'], strtotime($onechamp['date']));
                        $resultStats[$i][$r]['date'] = $newDate;
                        $r++;
                    }
                }

                $i++;
            }
          } else {
            $paramStatistics = null;
            $resultStats = null;
          }

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'activePage' => 'active',
            'paramStatistics' => $paramStatistics,
            'resultStats' => $resultStats,
        ]);
    }

    /**
     * @Route("/admin/list", name="list")
     */
    public function list(Request $request, ObjectManager $manager)
    {
        $class = $request->query->get('class');
        $pathClass = "App\Entity\\".$class;

        // Get Repository
        $repository = $this->getDoctrine()->getRepository("App\Entity\\".$class);

        // If Delete Click
        if($request->request->get('id')){
          $idToRemove = $request->request->get('id');
          $elementToRemove = $repository->find($idToRemove);
          $manager->remove($elementToRemove);
          $manager->flush();
          $this->addFlash('success', 'L\'objet a été supprimé avec succès');
        }

        //Get elements
        $elements = $repository->findAll();

        if($elements){
            // CONVERT OBJECT TO ARRAY for List
            foreach ($elements as $element) {
              $newElements[] = (array) $element;
            }
            //Filter for get each attributes
            $i = 0;
            foreach ($newElements as $newElement) {
              foreach ($newElement as $key => $value) {
                $newkey = str_replace("\x00", '', $key);
                $newkey = str_replace("App\Entity\\".$class, '', $newkey);
                $newElementsFilter[$i][$newkey] = $value;
              }
              $i++;
            }
        } else {
            $newElementsFilter = null;
        }

        // GET all attributes
        $elementTest = new $pathClass();
        $attributesClass = $elementTest->getAllAttributes();

        return $this->render('admin/list.html.twig', [
            'controller_name' => "Liste d'".$class,
            'class' => $class,
            'elements' => $newElementsFilter,
            'attributs' => $attributesClass,
        ]);
    }

    /**
     * @Route("/admin/edit", name="edit")
     */
    public function edit(Request $request, ObjectManager $manager)
    {
        $class = $request->query->get('class');
        $id = $request->query->get('id');
        $pathClass = "App\Entity\\".$class;

        //Get element
        $repository = $this->getDoctrine()->getRepository("App\Entity\\".$class);
        $element = $repository->find($id);

        // GET all attributes
        $elementTest = new $pathClass();
        $attributesClass = $elementTest->getAllAttributes();

        //Prepare read each attr of entity
        $docReader = new AnnotationReader();
        $reflect = new  \ReflectionClass($element);

        // Create FormBuilder with AttributesClass
        $form = $this->createFormBuilder($element);
        $i = 0;
        foreach ($attributesClass as $attr => $null) {
          if($i != 0){ //Ne pas ajouter le champ id
            //Récup chaque type d'attr
            $docInfos = $docReader->getPropertyAnnotations($reflect->getProperty($attr));
            $thisType = $docInfos[0]->type;
            if($thisType == "json"){
              $form->add($attr, "Symfony\Component\Form\Extension\Core\Type\CollectionType");
            } else {
              $form->add($attr);
            }
          }
          $i++;
        }
        $form = $form->add('save', "Symfony\Component\Form\Extension\Core\Type\SubmitType",[
                'label' => 'Enregistrer'
              ])
              ->getForm()
              ->handleRequest($request);

        //Traitement FormBuilder
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($element);
            $manager->flush();
            $this->addFlash('success', 'L\'objet a été modifié avec succès');
        }



        return $this->render('admin/edit.html.twig', [
            'controller_name' => "Editer un ".$class,
            'class' => $class,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/add", name="add")
     */
    public function add(Request $request, ObjectManager $manager)
    {
        $class = $request->query->get('class');
        $pathClass = "App\Entity\\".$class;

        //Get repository
        $repository = $this->getDoctrine()->getRepository("App\Entity\\".$class);

        // GET element & attributes
        $element = new $pathClass();
        $attributesClass = $element->getAllAttributes();

        //Prepare read each attr of entity
        $docReader = new AnnotationReader();
        $reflect = new  \ReflectionClass($element);

        // Create FormBuilder with AttributesClass
        $form = $this->createFormBuilder($element);
        $i = 0;
        foreach ($attributesClass as $attr => $null) {
          if($i != 0){ //Ne pas ajouter le champ id
            //Récup chaque type d'attr
            $docInfos = $docReader->getPropertyAnnotations($reflect->getProperty($attr));
            $thisType = $docInfos[0]->type;
            if($thisType == "json"){
              $form->add($attr, "Symfony\Component\Form\Extension\Core\Type\CollectionType");
            } else {
              $form->add($attr);
            }
          }
          $i++;
        }
        $form = $form->add('save', "Symfony\Component\Form\Extension\Core\Type\SubmitType",[
                'label' => 'Enregistrer'
              ])
              ->getForm()
              ->handleRequest($request);

        //Traitement FormBuilder
        if($form->isSubmitted() && $form->isValid()){
            $manager->persist($element);
            $manager->flush();
            $this->addFlash('success', 'L\'objet a été ajouté avec succès');
            return $this->redirectToRoute('list',[ 'class' => $class ]);
        }



        return $this->render('admin/edit.html.twig', [
            'controller_name' => "Ajouter un ".$class,
            'class' => $class,
            'form' => $form->createView(),
        ]);
    }
}
