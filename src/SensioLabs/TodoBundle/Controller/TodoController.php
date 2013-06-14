<?php

namespace SensioLabs\TodoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TodoController extends Controller
{
    public function randomAction()
    {
        $gateway = $this->container->get('sensiolabs.todo_gateway');

        $response = $this->render('SensioLabsTodoBundle:Todo:random.html.twig', array(
            'task' => $gateway->getRandomTask(),
        ));

        $response->setSharedMaxAge(20);

        return $response;
    }

    public function indexAction()
    {
        $gateway = $this->container->get('sensiolabs.todo_gateway');

        $tasks = $gateway->getAllTasks();

        $response = $this->render('SensioLabsTodoBundle:Todo:index.html.twig', array('tasks' => $tasks));
        $response->setSharedMaxAge(30);

        return $response;
    }

    public function taskAction($id)
    {
        $gateway = $this->container->get('sensiolabs.todo_gateway');

        if (!$task = $gateway->getTask($id)) {
            throw $this->createNotFoundException('No task found');
        }

        return $this->render('SensioLabsTodoBundle:Todo:task.html.twig', array('task' => $task));
    }

    public function newAction(Request $request)
    {
        $gateway = $this->container->get('sensiolabs.todo_gateway');
        $id = $gateway->createTask($request->request->get('title'));

        return $this->redirect($this->generateUrl('todo_task', array('id' => $id)));
    }
}
