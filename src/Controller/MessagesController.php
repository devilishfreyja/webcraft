<?php
namespace App\Controller;

use App\Entity\Contact;
use App\Entity\WebsiteInfo;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MessagesController extends Controller
{
	/**
	* @Route("/craft/inbox", name="inbox")
	*/
	public function displayInbox(Request $request) {

		$repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $inbox = $rep->findby([], ["messageDate" =>  "DESC"]);
        $messages = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        return $this->render('backoffice/user/inbox.html.twig',
            array(
                "sitetype"  =>  $query, "messages" => $messages, 'inbox'  =>  $inbox
            )
        );
	}

    /**
    * @Route("/craft/inbox/{id}", name="openmessage")
    */
    public function showMessage(Request $request, $id) {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $message = $rep->find($id);

        $em = $this->getDoctrine()->getManager();
        $lu = $em->getRepository(Contact::class)->find($id);
        $lu->setStatus("lu");
        $em->flush();

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        return $this->render('backoffice/user/message.html.twig', ["sitetype" =>  $query, "message" => $message, "messages" => $query2]
        );
    }

    /**
     * @Route("/craft/inbox/remove/{id}", name="deletemessage")
     */
    public function deleteMessageAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $contact = $em->getRepository(Contact::class)
            ->find($id);
        $em->remove($contact);
        $em->flush();
        $this->addFlash(
            'success',
            'Le message a bien été supprimé'
        );
        return $this->redirect($this->generateUrl('inbox'));
    }
}
