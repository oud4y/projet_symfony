<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;

class BookController extends AbstractController
{
    #[Route('/book/add', name: 'book_add')]
    public function add(Request $request, EntityManagerInterface $em)
    {
        $book = new Book();
        $book->setPublished(true); // publié par défaut

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $book->getAuthor();
            if ($author) {
                $author->setNbBooks($author->getNbBooks() + 1);
            }

            $em->persist($book);
            $em->flush();

            return $this->redirectToRoute('book_add'); // ou une autre page
        }

        return $this->render('book/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/books/published', name: 'book_published_list')]
    public function listPublishedBooks(BookRepository $bookRepository): Response
    {
        // Get only published books
        $books = $bookRepository->findBy(['published' => true]);

        // Count published and unpublished
        $publishedCount = $bookRepository->count(['published' => true]);
        $unpublishedCount = $bookRepository->count(['published' => false]);

        return $this->render('book/list.html.twig', [
            'books' => $books,
            'publishedCount' => $publishedCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }

    #[Route('/book/{id}/edit', name: 'book_edit')]
    public function edit(Request $request, Book $book, EntityManagerInterface $em)
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('book_published_list');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/book/{id}/delete', name: 'book_delete', methods: ['POST'])]
    public function delete(Book $book, EntityManagerInterface $em): Response
    {
        $author = $book->getAuthor();

        $em->remove($book);
        $em->flush();

        if ($author && $author->getNbBooks() <= 1) {
            $em->remove($author);
            $em->flush();
        } else {
            if ($author) {
                $author->setNbBooks($author->getNbBooks() - 1);
                $em->flush();
            }
        }

        return $this->redirectToRoute('book_published_list');
    }

    #[Route('/book/{id}/show', name: 'book_show')]
    public function show(Book $book): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }


}
