<?php
namespace App\Controller;

use App\Entity\WebsiteInfo;
use App\Form\Contact;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DesignController extends Controller
{
	/**
	* @Route("/craft/design", name="design")
	*/
	public function new(Request $request) {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );
        
        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findAll();

        return $this->render('backoffice/customs/design.html.twig',
            ["sitetype" =>  $query, "messages"  =>  $query2]
        );
	}
}
