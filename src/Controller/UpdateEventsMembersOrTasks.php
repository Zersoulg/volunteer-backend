<?php

namespace App\Controller;


use App\Entity\Event;
use Symfony\Component\HttpFoundation\Request;

class UpdateEventsMembersOrTasks
{
    public function __invoke(Event $data, Request $request)
    {
        $body = json_decode($request->getContent(),true);

        if (isset($body['members']) || isset($body['tasks'])) {
            return $data;
        }
    }
}