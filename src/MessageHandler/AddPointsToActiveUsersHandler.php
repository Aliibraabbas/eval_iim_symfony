<?php

namespace App\MessageHandler;

use App\Message\AddPointsToActiveUsers;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AddPointsToActiveUsersHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $em
    ) {}

    public function __invoke(AddPointsToActiveUsers $message)
    {
        $users = $this->userRepository->findBy(['actif' => true]);

        foreach ($users as $user) {
            $user->setPoints($user->getPoints() + 1000);
        }

        $this->em->flush();
    }
}
