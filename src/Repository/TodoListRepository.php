<?php

namespace App\Repository;

use App\Entity\TodoList;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TodoList>
 */
class TodoListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TodoList::class);
    }

    public function transform(TodoList $todoList)
    {
        return [
            'id'    => (int) $todoList->getId(),
            'title' => (string) $todoList->getTitle(),
            'deadline' => $todoList->getDeadline()->format('Y-m-d'),
            'status' => (int) $todoList->isStatus(),
        ];
    }

    public function transformAll()
    {
        $todoLists = $this->findAll();
        $todoListsArray = [];

        foreach ($todoLists as $list) {
            $todoListsArray[] = $this->transform($list);
        }

        return $todoListsArray;
    }

    //    /**
    //     * @return TodoList[] Returns an array of TodoList objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TodoList
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
