<?php
namespace App\Controller;

use App\Entity\WebsiteInfo;
use App\Entity\Pages;
use App\Entity\PageCategories;
use App\Entity\Contact;
use App\Entity\Menu;
use App\Form\AddPageType;
use App\Form\EditPageType;
use App\Form\AddPageCategoryType;
use App\Form\EditPageCategoryType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ManagePagesController extends Controller
{
    /**
    * @Route("/craft/newpage", name="newpage")
    */
    public function addPageAction(Request $request) {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );
        
        $user = $this->getUser();
        $user->getId();

        $rep = $this->getDoctrine()->getManager()->getRepository(Pages::class);
        $pages = $rep->findAll();

        $getCats = $this->getDoctrine()->getManager()->getRepository(PageCategories::class);
        $cats = $getCats->findAll();

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        $cat = new PageCategories();
        $catForm = $this->createForm(AddPageCategoryType::class, $cat);

        $catForm->handleRequest($request);
        if ($catForm->isSubmitted() && $catForm->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($cat);
            $em->flush();
            $this->addFlash(
                'success',
                "Catégorie créée !"
            );
            return $this->redirectToRoute('newpage');
        }

        $page = new Pages();
        $form = $this->createForm(AddPageType::class, $page);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manyMenu = $this->getDoctrine()->getManager()->getRepository(Menu::class);
            $menus = $manyMenu->findAll();

            if (count($menus) < 5) {

                $addInMenu = new Menu();
                $em = $this->getDoctrine()->getManager();
                $addInMenu->setMenuRank(count($menus) + 1);
                $addInMenu->setPageName($_POST["add_page"]["page_name"]);
                $em->persist($addInMenu);
                $em->flush();
            }

            $text = preg_replace('~[^\pL\d]+~u', '-', $_POST["add_page"]["page_name"]);
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            $text = preg_replace('~[^-\w]+~', '', $text);
            $text = trim($text, '-');
            $text = preg_replace('~-+~', '-', $text);

            $page->setUserId($user->getId());
            $page->setPageName($text);
            $page->setPageDate(new \DateTime('now'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();
            $this->addFlash(
                'success',
                "Page créée !"
            );
            return $this->redirectToRoute('managepages');
        }

        return $this->render('backoffice/pages/addpage.html.twig',
            ["sitetype" =>  $query, 'form' => $form->createView(), "pages" => $pages, "categories" => $cats, 'catForm' => $catForm->createView(),
                 "messages"  =>  $query2]
        );
    }

	/**
	* @Route("/craft/pages/manage", name="managepages")
	*/
	public function displayPagesAction(Request $request) {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $rep = $this->getDoctrine()->getManager()->getRepository(Pages::class);
        $pages = $rep->findAll();

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        $getCats = $this->getDoctrine()->getManager()->getRepository(PageCategories::class);
        $cats = $getCats->findAll();

        return $this->render('backoffice/pages/managepages.html.twig',
            ["sitetype" =>  $query, "pages" => $pages, "categories" => $cats, "messages"  =>  $query2]
        );
	}

    /**
     * @Route("/craft/pages/edit/{id}", name="editpage")
     */
    public function editPageAction(Request $request, $id)
    {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $getPages = $this->getDoctrine()->getManager()->getRepository(Pages::class);
        $pages = $getPages->findAll();

        $page = $this->getDoctrine()
            ->getManager()
            ->getRepository(Pages::class)
            ->find($id)
        ;

        $_SESSION["pagename"] = "";
        $_SESSION["pagename"] = $page->getPageName();

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        $getCats = $this->getDoctrine()->getManager()->getRepository(PageCategories::class);
        $cats = $getCats->findAll();

        $cat = new PageCategories();
        $catForm = $this->createForm(AddPageCategoryType::class, $cat);

        $catForm->handleRequest($request);
        if ($catForm->isSubmitted() && $catForm->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($cat);
            $em->flush();
            $this->addFlash(
                'success',
                "Catégorie créée !"
            );
            return $this->redirectToRoute('editpage', ["id"  =>  $id]);
        }

        $form = $this->createForm(EditPageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {

            $page->setPageModified(new \DateTime('now'));
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash(
                'success',
                "Page mise à jour !"
            );

            $em = $this->getDoctrine()->getManager();
            $menu = $em->getRepository(Menu::class)
                ->findOneBy(
                    [
                        "pageName"  =>  $_SESSION["pagename"]
                    ]
                );
            $_SESSION["pagename"] = "";

            if (!empty($menu)) {
                $menu->setPageName($_POST["edit_page"]["page_name"]);
                $em->flush();
            }

            return $this->redirect($this->generateUrl('managepages'));
        }

        return $this->render('backoffice/pages/editpages.html.twig', ["sitetype" =>  $query, 'form' => $form->createView(), "page" => $page, 'pages' => $pages, "categories" => $cats, 'catForm' => $catForm->createView(),  "messages"  =>  $query2]
        );
    }

    /**
     * @Route("/craft/pages/remove/{id}", name="deletepage")
     */
    public function deletePageAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $page = $em->getRepository(Pages::class)
            ->find($id);

        $nameSave = $page->getPageName();

        $em->remove($page);
        $em->flush();

        $em = $this->getDoctrine()->getManager();
        $menu = $em->getRepository(Menu::class)
            ->findOneBy(
                [
                    "pageName"  =>  $nameSave
                ]
            );

        if (!empty($menu)) {
            $em->remove($menu);
            $em->flush();

            $this->addFlash(
                'success',
                'Page supprimée'
            );
        }

        return $this->redirect($this->generateUrl('managepages'));
    }

    /**
    * @Route("/craft/pages/categories", name="pagecategories")
    */
    public function displayPageCategoriesAction(Request $request) {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $rep2 = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep2->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        $rep = $this->getDoctrine()->getManager()->getRepository(PageCategories::class);
        $cats = $rep->findAll();

        $cat = new PageCategories();
        $form = $this->createForm(AddPageCategoryType::class, $cat);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($cat);
            $em->flush();
            $this->addFlash(
                'success',
                "Catégorie créée !"
            );
            return $this->redirectToRoute('pagecategories');
        }

        return $this->render('backoffice/pages/pagecategories.html.twig',
            ["sitetype" =>  $query, "categories" => $cats, "form" => $form->createView(), "messages"  =>  $query2]
        );
    }

    /**
     * @Route("/craft/pages/categories/edit/{id}", name="editpagecategories")
     */
    public function editPageCategoriesAction(Request $request, $id)
    {

        $repository = $this->getDoctrine()->getManager()->getRepository(WebsiteInfo::class);
        $query = $repository->findOneBy(
            ["sitetype" =>  "2"]
        );

        $rep = $this->getDoctrine()->getManager()->getRepository(Contact::class);
        $query2 = $rep->findBy(
            [
                "status"    =>  "nonlu"
            ]
        );

        $em = $this->getDoctrine()->getManager()->getRepository(PageCategories::class);
        $cats = $em->findAll();

        $cat = $this->getDoctrine()
            ->getManager()
            ->getRepository(PageCategories::class)
            ->find($id)
        ;

        $form = $this->createForm(EditPageCategoryType::class, $cat);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash(
                'success',
                "Page mise à jour !"
            );
            return $this->redirect($this->generateUrl('managepages'));
        }

        return $this->render('backoffice/pages/editpagecategories.html.twig', ["sitetype" =>  $query, 'form' => $form->createView(), "category" => $cat, 'categories' => $cats, "messages"  =>  $query2]
        );
    }

    /**
     * @Route("/craft/pages/categories/remove/{id}", name="deletepagecategory")
     */
    public function deletePageCategoryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $cat = $em->getRepository(PageCategories::class)
            ->find($id);
        $em->remove($cat);
        $em->flush();
        $this->addFlash(
            'success',
            'Catégorie de page supprimée'
        );
        return $this->redirect($this->generateUrl('pagecategories'));
    }
}
