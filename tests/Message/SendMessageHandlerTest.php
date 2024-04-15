<?php

namespace App\Tests\Message;

use App\Entity\Message;
use App\Message\SendMessage;
use App\Message\SendMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendMessageHandlerTest extends TestCase
{

    private MockObject $messageMock;
    private MockObject $entityManagerMock;
    private SendMessageHandler $sendMessageHandler;

    protected function setUp(): void
    {
        $this->messageMock = $this->createMock(Message::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);

        $this->sendMessageHandler = new SendMessageHandler(
            $this->entityManagerMock
        );
    }

    public function test__invoke(): void
    {
        $this->messageMock->expects($this->any())
            ->method('setUuid')
            ->willReturnSelf();

        $this->messageMock->expects($this->any())
            ->method('setText')
            ->willReturnSelf();

        $this->messageMock->expects($this->any())
            ->method('setStatus')
            ->willReturnSelf();

        $this->messageMock->expects($this->any())
            ->method('setCreatedAt')
            ->willReturnSelf();

        $this->entityManagerMock->expects($this->any())
            ->method('persist')
            ->willReturnSelf();

        $this->entityManagerMock->expects($this->any())
            ->method('flush')
            ->willReturnSelf();

        $sendMessage = new SendMessage('Text');

        $this->sendMessageHandler->__invoke($sendMessage);
        $this->assertInstanceOf(Message::class, $this->messageMock);
    }
}
