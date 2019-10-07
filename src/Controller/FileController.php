<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Document\FileProperty;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Property controller.
 *
 * @Route("api/")
 */
class FileController extends BaseController
{
    /**
     * @Route(
     *     "entities/{uuid}/embedded/{propertyName}/{propertyId}/file/{fileProperty}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "propertyName" = "^(\w{1,50})$",
     *          "fileProperty" = "^(\w{1,50})$",
     *          "propertyId" = "^(\w{1,50})$"
     *     },
     *     name="post_file",
     *     methods={"POST"})
     */
    public function postFileToEmbeddedDocumentAction(string $uuid, string $propertyName, string $fileProperty, string $propertyId, Request $request)
    {
        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $property = $entity->getPropertiesByKind($propertyName)[$propertyId];


        if(empty($property))
        {
            return $this->response->notFound("Resource not found");
        }

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$entity->getValidationState()]['methods'][$request->getMethod()]['properties'];

        if(!in_array($propertyName, $entityConfig))
        {
            return $this->response->notFound("Resource not found");
        }

        $embeddedEntity = $this->schemaLoader->loadEntityEnumeration($propertyName)['properties'];
        if(!in_array($fileProperty, $embeddedEntity))
        {
            return $this->response->notFound("Resource not found");
        }

        $propertyConfig = $this->schemaLoader->loadPropertyEnumeration($fileProperty);
        if($propertyConfig['type'] !== 'file')
        {
            return $this->response->notFound("Resource not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        if(empty($request->getContent()))
        {
            return $this->response->badRequest("Request must not be empty and must provide a raw file");
        }

        $allowedMultiple = array_key_exists('array',$propertyConfig) && $propertyConfig['array'] === true;

        if(count($property->getFiles())>=1 && !$allowedMultiple)
        {
            return $this->response->badRequest("A file has already been posted to this property");
        }

        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mimeTypeProvided = $file_info->buffer($request->getContent());

        if(!in_array($mimeTypeProvided,$propertyConfig['constraints']['mime']))
        {
            return $this->response->badRequest("Bad mimetype. Accepted files are : ".json_encode($propertyConfig['constraints']['mime']));
        }

        $file = new FileProperty(['uuid'=>$user->getUuidAsString(), 'username'=>$user->getUsername()]);
        $file->setFile($request->getContent());
        $file->setFilename($request->headers->get('X-Filename'));
        $file->setDescription($request->headers->get('X-Description'));
        $file->setCredit($request->headers->get('X-Credit'));
        $file->setMimetype($mimeTypeProvided);

        $property->addFiles($file);
        $this->dm->flush();

        $this->fileuploader->persistFile($file);

        return $this->response->created($entity, ['entity_basic', 'entity_id', 'user_basic']);
    }
}