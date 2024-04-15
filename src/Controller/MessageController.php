<?php
declare(strict_types=1);

namespace App\Controller;

use App\Message\SendMessage;
use App\Repository\MessageRepository;
use Controller\MessageControllerTest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see MessageControllerTest
 * TODO: review both methods and also the `openapi.yaml` specification
 *       Add Comments for your Code-Review, so that the developer can understand why changes are needed.
 */
class MessageController extends AbstractController
{

    /**
     * @param MessageRepository $messageRepository
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private readonly MessageRepository   $messageRepository,
        private readonly MessageBusInterface $messageBus
    ) {
    }

    /**
     * TODO: cover this method with tests, and refactor the code (including other files that need to be refactored)
     */
    #[Route('/messages', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $messages = $this->messageRepository->by($request);
  
        foreach ($messages as $key => $message) {
            $messages[$key] = [
                'uuid' => $message->getUuid(),
                'text' => $message->getText(),
                'status' => $message->getStatus(),
            ];
        }

        $encoders = [new JsonEncoder()];
        $normalizers = [new ArrayDenormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        $json = $serializer->serialize($messages, 'json');

        //TODO add handling if the result is null(no messages)

        return new Response($json, headers: ['Content-Type' => 'application/json']);
    }

    #[Route('/messages/send', methods: ['GET'])]
    public function send(Request $request): Response
    {

        $text = $request->query->get('text');

        //Added validation for blank text, and special characters to prevent SQL injection
        $validator = Validation::createValidator();
        $errors = $validator->validate(
            $text,
            [
                new Assert\NotBlank(),
                new Assert\Regex(
                    pattern: '/[^a-z0-9\s\.\-\?\+\,]/i',
                    message: 'Your text cannot contain special characters',
                    match: false
                )
            ]
        );

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new Response(implode("\n", $errorMessages), 400);
        }

        $this->messageBus->dispatch(new SendMessage((string)$text));

        return new Response('Successfully sent', 200);
    }
}