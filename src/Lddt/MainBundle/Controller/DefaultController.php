<?php

namespace Lddt\MainBundle\Controller;

use Doctrine\Bundle\DoctrineCacheBundle\Command\DeleteCommand;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Id;
use Lddt\MainBundle\Entity\Category;
use Lddt\MainBundle\Entity\Color;
use Lddt\MainBundle\Entity\Comment;
use Lddt\MainBundle\Entity\Draw;
use Lddt\MainBundle\Entity\Tag;
use Lddt\MainBundle\Form\CommentType;
use Lddt\MainBundle\Form\DrawType;
use Lddt\MainBundle\Form\FormHandler;
use Lddt\MainBundle\LddtMainBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        //Recupérer les dessins
        $draws = $this->get('doctrine')
                      ->getRepository('LddtMainBundle:Draw')
                      ->findAllDraws();
        //Passage des données à la vue, les clés du tableau associatif deviennent le nom des variables dans les vues twig
        $datas = ['draws'=>$draws];
        return $this->render('LddtMainBundle:Default:index.html.twig',$datas);
    }

   /* public function showAction(Request $request) {
        $id = $request->get('id');
/*        echo $id;=>
        die ();*/
       /* $draw = $this->get('doctrine')->getRepository('LddtMainBundle:Draw')
        ->findOneBy(array('id'=>$id));
        $datas = ['draw'=>$draw];
        return $this->render('LddtMainBundle:Default:show.html.twig',$datas);
    }*/

    /**
     * @Template()
     */

    public function showAction(Draw $draw,Request $request) {
        $user = $this->getUser();
        $is_proprio = false;
        if ($user == $draw->getAuthor()) {
            $is_proprio = true;
        }
        $datas = ['draw'=>$draw, 'is_proprio'=>$is_proprio];
        if ($user) {
            $form = $this->createForm(CommentType::class,new Comment($draw, $user));
            $datas =['draw'=>$draw,'form_comment'=>$form->createView(),'is_proprio'=>$is_proprio];
            $em = $this->get('doctrine')->getManager();
            $formHandler = new FormHandler($form,$request,$em);
            if($formHandler->process()) {
            $this->addFlash('success','Le commentaire a été prit en compte');
            return $this->redirect($this->generateUrl('lddt_main_show',['id'=>$draw->getId()]));
            }
        }

        return $datas;


       /*
        return $datas;*/
    }


    /*public function createAction() {
        //1-Instanciation de l'entité Category et hydratation
        $category = new Category();
        $category->setName("nature");
        //1-ter> créa des couleurs
        $color = new Color();
        $color->setName('rouge');
        $color->setCode('FF0000');

        $color2 = new Color();
        $color2->setName('bleu');
        $color2->setCode('000FFE');

        $color3 = new Color();
        $color3->setName('noir');
        $color3->setCode('000000');

        //1-Bis> Instanciation de l'entité Draw et hydratation
        $draw = new Draw();
        $draw->setTitle('chaussure');
        $draw->setDrawPath('chaussure.jpg');
        $draw->setIsOnline(true);
        $draw->setAuthorName('charlie');
        $draw->setAvatarPath('charlie-ico.jpg');
        $draw->setCreatedAt(new \DateTime());
        $draw->setUpdatedAt(new \DateTime());
        //On lie la categorie et le dessin
        $draw->setCategory($category);
        //On lie les couleurs au dessin
        $draw->addColor($color);
        $draw->addColor($color2);
        $draw->addColor($color3);
        //2> Appel de l'entity Manager de Doctrine
        $em = $this->get('doctrine')->getManager();
        //> On persiste l'instance (on prépare la requête)
        $em->persist($category);
        $em->persist($color);
        $em->persist($color2);
        $em->persist($color3);
        $em->persist($draw);
        //> On execute la requête
        $em->flush();
        return $this->redirect($this->generateUrl('lddt_main_homepage'));
    }*/

    public function createAction(Request $request) {
        //Appel de l'instance DrawTyper (pour afficher le formulaire)
        //Récupérer l'utilisateur
        $author =$this->getUser();
//        var_dump($author);
//        die();
        $datas = [];
        if ($author) {
        $form = $this->createForm(DrawType::class,new Draw($author));
        $datas =['form'=>$form->createView()];

        $em = $this->get('doctrine')->getManager();
        $formHandler = new FormHandler($form,$request,$em);
        if($formHandler->process()) {
            $this->addFlash('success','le dessin a bien été créé, il est en attente de confirmation');
            return $this->redirect($this->generateUrl('lddt_main_homepage'));
            }
        }

        return $this->render ('LddtMainBundle:Default:create.html.twig',$datas);
    }

    public function deleteAction(draw $draw) {
        if(!$this->checkAuthorization($draw->getAuthor())) {
            return $this->redirect($this->generateUrl('lddt_main_homepage'));
        }
            $em = $this->get('doctrine')->getManager();
            $em->remove($draw);
            $em->flush();{
            $this->addFlash('success','le dessin a bien été supprimé');
            return $this->redirect($this->generateUrl('lddt_main_homepage'));
        }
    }

    public function editAction(Draw $draw,Request $request) {
        if(!$this->checkAuthorization($draw->getAuthor())) {
            return $this->redirect($this->generateUrl('lddt_main_homepage'));
        }
        //Appel de l'instance DrawTyper (pour afficher le formulaire)
        $form = $this->createForm(DrawType::class,$draw);
        $datas =['form'=>$form->createView(),'draw'=>$draw];
        $em = $this->get('doctrine')->getManager();
        $formHandler = new FormHandler($form,$request,$em);
        if($formHandler->process()) {
            $this->addFlash('success','le dessin a bien été modifié');
            return $this->redirect($this->generateUrl('lddt_main_homepage'));
        }
        return $this->render ('LddtMainBundle:Default:edit.html.twig',$datas);
    }


    /**
     * @Template("LddtMainBundle:Default:index.html.twig")
     * @param Category $category
     */
    public function listDrawsbyCatAction(Category $category) {
        //req DQL pour filtrer les dessins d'une catégorie
        $draws = $this->get('doctrine')->getRepository("LddtMainBundle:Draw")->findAllDrawsByCat($category);
        $datas = ['draws'=>$draws,'category'=>$category];
        return $datas;
    }

    /**
     * @Template("LddtMainBundle:Default:index.html.twig")
     * @param Color $color
     * @return array
     */
    public function  listDrawsByColorAction(Color $color) {
        //req DQL pour filtrer les dessins d'une couleur
        $draws = $this->get('doctrine')->getRepository("LddtMainBundle:Draw")
            ->findAllDrawsByColor(array($color->getName()));

        $datas = ['draws'=>$draws,'color'=>$color];
        return $datas;
    }

    /**
     * @Template("LddtMainBundle:Default:index.html.twig")
     * @param Tag $tag
     * @return array
     */
    public function listDrawsByTagAction(Tag $tag) {
        $draws = $this->get('doctrine')->getRepository("LddtMainBundle:Draw")
            ->findAllDrawsByTag(array($tag->getName()));

        $datas = ['draws'=>$draws,'tag'=>$tag];
        return $datas;

    }

    private function checkAuthorization($instance) {
        //return true si le user est admin
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return true;
         //return true si le user est proprio
        } else if($this->getUser() == $instance) {
          return true;
        }
        //return redirect vers l'URL si le user n'est pas autorisé
        else {
        return false;

        }
    }
}

