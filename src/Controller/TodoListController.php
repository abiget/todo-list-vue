<?php

namespace App\Controller;

use App\Entity\TodoList;
use App\Repository\TodoListRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TodoListController extends ApiController
{
    #[Route('/api/todo/list', methods: "GET", name: 'app_todo_list')]
    public function index(TodoListRepository $todoListRepository)
    {
        $lists = $todoListRepository->transformAll();

        return $this->respond([
            'lists' => $lists,
        ]);
    }


    #[Route('/api/todo/list/create', methods: "POST", name: 'app_todo_list_create')]
    public function create(Request $request, TodoListRepository $todoListRepository, EntityManagerInterface $em)
    {
        if (!$request) {
            return $this->respondValidationError('Please provide a valid request!');
        }

        // validate the title
        if (!$request->get('title')) {
            return $this->respondValidationError('Please provide a title!');
        }
        if (!$request->get('deadline')) {
            return $this->respondValidationError('Please provide a deadline!');
        }

        // persist the new todolist
        $todoList = new TodoList;
        $todoList->setTitle($request->get('title'));
        $todoList->setDeadline(new DateTime($request->get('deadline')));
        $todoList->setStatus(0); //default to incomplet todo 
        $em->persist($todoList);
        $em->flush();

        return $this->respondCreated($todoListRepository->transform($todoList));
    }

    #[Route('/api/todo/list/{id}/edit', methods: "POST", name: 'app_todo_list_edit')]
    public function edit(TodoList $todoList, Request $request, TodoListRepository $todoListRepository, EntityManagerInterface $em)
    {
        if (!$todoList) {
            return $this->respondNotFound();
        }

        $todoList->setTitle($request->get('title'));
        $todoList->setDeadline(new DateTime($request->get('deadline')));
        $em->persist($todoList);
        $em->flush();

        return $this->respondUpdated($todoListRepository->transform($todoList));
    }
    
    #[Route("/api/todo/list/{id}/delete", name: "app_todo_list_delete", methods: "DELETE")]
    public function delete(TodoList $todoList, Request $request, TodoListRepository $todoListRepository, EntityManagerInterface $em)
    {
        if (!$todoList) {
            return $this->respondNotFound();
        }
        
        $em->remove($todoList);
        $em->flush();

        // Redirect to a page or show a message
        return $this->respondDeleted('Deleted Sucessfully'); // Redirect to your index page
    }

    
    #[Route('/api/todo/list/{id}/status', methods: "POST", name: 'app_todo_list_edit')]
    public function editStatus(TodoList $todoList, Request $request, TodoListRepository $todoListRepository, EntityManagerInterface $em)
    {
        if (!$todoList) {
            return $this->respondNotFound();
        }
    
        $todoList->setStatus($request->get('status'));
        $em->persist($todoList);
        $em->flush();
    
        return $this->respondUpdated('List updated!');
    }
}
