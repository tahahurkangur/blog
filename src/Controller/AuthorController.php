<?php

namespace App\Controller;

use App\Entity\BlogPost;
use App\Entity\User;
use App\Form\ContentAddType;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/admin/author/{id}", name="author_page", methods={"GET"})
     * @Template()
     */
    public function indexAction($id): array
    {
        $contents = $this->em->getRepository(BlogPost::class)->findBy(array('author'=>$id));
        return array(
            'contents' => $contents
        );
    }

    /**
     * @Route("/admin/author/create", name="author_create")
     */
    public function createAuthorAction(Request $request, UserService $userService){
        $author=$this->getUser();
        $author->setRoles(array_merge($author->getRoles(), ['ROLE_AUTHOR']));
        $this->em->persist($author);
        $this->em->flush($author);
        $this->addFlash('success', 'Tebrikler! Artık yazarsınız.');
        $userService->loginAsUser($author);
        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/admin/author/content/add", name="content_add", methods={"GET","POST"})
     * @Template()
     */
    public function addContent(Request $request){
        $content = new BlogPost();
        //$author = $this->em->getRepository(User::class)->find($this->getUser()->getId());
        $form = $this->createForm(ContentAddType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $content->setAuthor($this->em->getRepository(User::class)->find($this->getUser()->getId()));
            $this->em->persist($content);
            $this->em->flush();
            return $this->redirect($this->generateUrl('content_show', array('id' => $content->getId())));

        }
        return array(
            'form'   => $form->createView(),
        );

    }

    /**
     * @Route("/admin/author/content/{id}", name="content_show", methods={"GET","POST"})
     * @Template()
     */
    public function showContent($id, BlogPost $blogPost){
        $content = $this->em->getRepository(BlogPost::class)->find($id);
        if (!$content) {
            throw $this->createNotFoundException('Unable to find BlogPost entity.');
        }
        return array(
            'content' => $content
        );
    }

    /**
     * @Route("/admin/author/content/{id}/delete", name="content_delete", methods={"DELETE","GET"})
     */
    public function deleteContent($id, Request $request){
        $content = $this->em->getRepository(BlogPost::class)->find($id);
        if (!$content) {
            throw $this->createNotFoundException('Unable to find BlogPost entity.');
        }
        $this->em->remove($content);
        $this->em->flush();
        return $this->redirect($this->generateUrl('homepage'));

    }

    /**
     * @Route("/admin/author/content/{id}/edit", name="content_edit", methods={"GET","POST"})
     * @Template()
     */
    public function editContent($id, Request $request){
        $content=$this->em->getRepository(BlogPost::class)->find($id);
        if (!$content) {
            throw $this->createNotFoundException('Unable to find BlogPost entity.');
        }
        $form = $this->createForm(ContentAddType::class, $content);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $this->em->persist($content);
            $this->em->flush();
            return $this->redirect($this->generateUrl('content_show', array('id' => $content->getId())));
        }
        return array(
            'form'   => $form->createView(),
        );

    }

}