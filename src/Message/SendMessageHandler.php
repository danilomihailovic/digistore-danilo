<?php
declare(strict_types=1);

namespace App\Message;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
class SendMessageHandler
{
    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(
        private readonly EntityManagerInterface $manager
    ) {
    }

    public function __invoke(SendMessage $sendMessage): void
    {
        $message = new Message();
        $message->setUuid(Uuid::v6()->toRfc4122());
        $message->setText($sendMessage->text);
        $message->setStatus('sent');
        $message->setCreatedAt(new \DateTime());

        $this->manager->persist($message);
        $this->manager->flush();
    }
}