<?php
namespace Lddt\MainBundle\Form;


use Doctrine\ORM\EntityManager;
use Lddt\MainBundle\Entity\Draw;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class FormHandler
{
    private $form; //formulaire
    private $request; //Les requetes recupérés
    private $em; // Entite Manager de doctrine

    public function __construct(Form $form,Request $request,EntityManager $em)
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;

    }
    public function process(){
        if($this->request->getMethod() == "POST"){
            $this->form->handleRequest($this->request);
            if($this->form->isValid() == true) {
                //On persiste les données
                $this->onSuccess($this->form->getData());
                //On return true
                return true;
            }
        }
    return false;
    }
    private function onSuccess($instance) {
        $this->em->persist($instance);
        $this->em->flush();

    }

}