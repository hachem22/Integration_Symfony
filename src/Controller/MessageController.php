<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Message;
use App\Form\MessageType;
use App\Entity\Blog;
use App\Entity\Utilisateur;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageController extends AbstractController
{
    #[Route('/message/new/{blog_id}', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $blog_id, EntityManagerInterface $entityManager): Response
    {
        // Ensure the user is logged in
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('You must be logged in to send a message.');
        }

        // Fetch the blog post
        $blog = $entityManager->getRepository(Blog::class)->find($blog_id);
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found.');
        }

        // Fetch the author of the blog post
        $author = $blog->getAuthor();
        if (!$author) {
            throw $this->createNotFoundException('Author not found.');
        }

        // Create a new message
        $message = new Message();
        $message->setDateEnvoi(new \DateTime('now'));
        $message->setSenderName($user->getNom() . ' ' . $user->getPrenom()); // Set the sender name
        $message->setAuthor($author); // Set the author (recipient) of the message
        $message->setBlog($blog); // Associate the message with the blog

        // Create the form
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Your message has been sent to the author.');
            return $this->redirectToRoute('app_blog_show', ['id' => $blog_id]);
        }

        // Render the form
        return $this->render('message/new.html.twig', [
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }

    #[Route('/message', name: 'app_message_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch all messages
        $messages = $entityManager->getRepository(Message::class)->findAll();

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/message/show/{author_id}', name: 'app_message_show', methods: ['GET'])]
    public function show(int $author_id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the author
        $author = $entityManager->getRepository(Utilisateur::class)->find($author_id);
        if (!$author) {
            throw $this->createNotFoundException('Author not found.');
        }

        // Fetch messages for the author
        $messages = $entityManager->getRepository(Message::class)->findBy(['author' => $author]);

        if (!$messages) {
            $this->addFlash('info', 'No messages found.');
        }

        return $this->render('message/show.html.twig', [
            'author' => $author,
            'messages' => $messages,
        ]);
    }

    #[Route('/message/edit/{id}', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Your message has been updated.');
            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/message/delete/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $message->getId(), $request->request->get('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
            $this->addFlash('success', 'Message deleted successfully.');
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/message/{id}', name: 'app_message_show_one', methods: ['GET'])]
    public function showOne(Message $message): Response
    {
        return $this->render('message/show_one.html.twig', [
            'message' => $message,
        ]);
    }
}