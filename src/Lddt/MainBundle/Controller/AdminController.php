<?php

namespace Lddt\MainBundle\Controller;


use Lddt\MainBundle\Entity\Draw;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    /**
     * @Template()
     */
    public function indexAction () {
        $draws = $this->get('doctrine')
            ->getRepository('LddtMainBundle:Draw')
            ->findAllDrawToPushOnLine();
        $datas = ['draws'=>$draws];
        return $datas;
    }

    public function pushOnlineAction(Draw $draw) {
        $draw->setIsOnLine(true);
        $em = $this->get('doctrine')->getManager();
        $em->persist($draw);
        $em->flush();
        // envoie du mail de confirmation de mise en ligne
        $message = \Swift_Message::newInstance()
        ->setSubject("Votre dessin {$draw->getTitle()}")
        ->setFrom('jaypasdepoe95@gmail.com')
        ->setTo($draw->getAuthor()->getEmail())
        ->setBody($this->renderView('@LddtMain/Email/confirmation_online.html.twig',['draw'=>$draw]),'text/html');
        $this->get('mailer')->send($message);

        $this->addFlash('success','le dessin'.$draw->getTitle().'est en ligne');
        return $this->redirect($this->generateUrl('lddt_admin_homepage'));
    }

}