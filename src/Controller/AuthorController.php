<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\AuthorType;
use Symfony\Component\HttpFoundation\Request;

class AuthorController extends AbstractController
{
    #[Route('/authors/list', name: 'author_list')]
    public function listAuthors(EntityManagerInterface $em): Response
    {
        $authors = $em->getRepository(Author::class)->findAll();

        return $this->render('author/list.html.twig', [
            'authors' => $authors,
        ]);
    }


    #[Route('/author/details/{id}', name: 'author_details')]
    public function authorDetails(int $id): Response
    {
        $authors = [
            ['id' => 1, 'picture' => '/images/Victor-Hugo.jpg', 'username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com', 'nb_books' => 100],
            ['id' => 2, 'picture' => '/images/william-shakespeare.jpg', 'username' => 'William Shakespeare', 'email' => 'william.shakespeare@gmail.com', 'nb_books' => 200],
            ['id' => 3, 'picture' => '/images/Taha_Hussein.jpg', 'username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_books' => 300],
        ];

        $author = null;
        foreach ($authors as $a) {
            if ($a['id'] === $id) {
                $author = $a;
                break;
            }
        }

        if (!$author) {
         throw $this->createNotFoundException('Author not found');
        }

         return $this->render('author/showAuthor.html.twig', [
            'author' => $author,
        ]);
    }
    #[Route('/author/addStatic', name: 'author_add_static')]
    public function addAuthorStatic(EntityManagerInterface $em): Response
    {
        $author = new Author();
        $author->setUsername("Static Author");
        $author->setEmail("static.author@gmail.com");
        $author->setPicture("/images/default.jpg");
        $author->setNbBooks(0);

        $em->persist($author);
        $em->flush();

        return new Response("Author added successfully!");
    }
    #[Route('/author/add', name: 'author_add')]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('author_list');
        }

        return $this->render('author/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/author/edit/{id}', name: 'author_edit')]
    public function edit(Request $request, Author $author, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('author_list');
        }

        return $this->render('author/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/author/delete/{id}', name: 'author_delete')]
    public function delete(Author $author, EntityManagerInterface $em): Response
    {
        $em->remove($author);
        $em->flush();
        return $this->redirectToRoute('author_list');
    }




}
