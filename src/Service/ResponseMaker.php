<?php

namespace Deozza\PhilarmonyBundle\Service;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResponseMaker
{
    const BAD_REQUEST = 400;
    const NOT_FOUND = 404;
    const NOT_AUTHORIZED = 401;
    const FORBIDDEN_ACCESS = 403;
    const CONFLICT = 409;
    const CREATED = 201;
    const OK = 200;
    const EMPTY = 204;
    const CONTENT_TYPE = ['Content-Type'=>"application/json"];

    public function __construct(SerializerInterface $serializer)
    {
        $this->response = new JsonResponse();
        $this->response->headers->add(self::CONTENT_TYPE);
        $this->serializer = $serializer;
    }

    public function badRequest($message)
    {
        $this->response->setStatusCode(self::BAD_REQUEST);
        $this->response->setContent(json_encode(["error"=>$message]));
        return $this->response;
    }

    public function notFound($message, $resource)
    {
        $this->response->setStatusCode(self::NOT_FOUND);
        $this->response->setContent(
            json_encode(
                [
                    "error" => sprintf($message, $resource)
                ]
            )
        );
        return $this->response;
    }

    public function notAuthorized($message = null)
    {
        $this->response->setStatusCode(self::NOT_AUTHORIZED);
        return $this->response;
    }

    public function forbiddenAccess($message = null)
    {
        $this->response->setStatusCode(self::FORBIDDEN_ACCESS);
        $this->response->setContent(
            json_encode(
                [
                    "error" => $message
                ]
            )
        );
        return $this->response;
    }

    public function conflict($message = null, $context = null)
    {
        $this->response->setStatusCode(self::CONFLICT);
        $this->response->setContent(
            json_encode(
                [
                    "error" => $message,
                    "context" => $context
                ]
            )
        );
        return $this->response;
    }

    public function created($item, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::CREATED);
        $serialized = $this->serializer->serialize($item, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialized);
        return $this->response;
    }

    public function ok($item = null, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::OK);
        $serialized = $this->serializer->serialize($item, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialized);
        return $this->response;
    }

    public function okPaginated($item = null, $serializerGroups = ['Default'])
    {
        $this->response->setStatusCode(self::OK);
        $content = [
            "current_page_number"=>$item->getCurrentPageNumber(),
            "num_items_per_page"=>$item->getItemNumberPerPage(),
            "items"=>$item->getItems(),
            "total_count"=>$item->getTotalItemCount()
        ];
        $serialied = $this->serializer->serialize($content, 'json', SerializationContext::create()->setGroups($serializerGroups));
        $this->response->setContent($serialied);
        return $this->response;
    }

    public function empty()
    {
        $this->response->setStatusCode(self::EMPTY);
        return $this->response;
    }
}