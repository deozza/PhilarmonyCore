<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Document\Entity;
use Deozza\PhilarmonyCoreBundle\Document\FileProperty;
use Deozza\PhilarmonyCoreBundle\Document\Property;
use SebastianBergmann\CodeCoverage\Node\File;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     *     "entities/embedded/{uuid}/file/{fileProperty}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "fileProperty" = "^(\w{1,50})$"
     *     },
     *     name="post_file",
     *     methods={"POST"})
     */
    public function postFileToEmbeddedDocumentAction(string $uuid, string $fileProperty, Request $request)
    {
        $property = $this->dm->getRepository(Property::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($property))
        {
            return $this->response->notFound("Route not found");
        }

        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$property->getEntity()]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $entityConfig = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'][$entity->getValidationState()]['methods'][$request->getMethod()]['properties'];

        if(!in_array($property->getPropertyName(), $entityConfig))
        {
            return $this->response->notFound("Resource not found");
        }

        $embeddedEntity = $this->schemaLoader->loadEntityEnumeration($property->getPropertyName())['properties'];
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

        $file = new FileProperty(['uuid'=>$user->getUuidAsString(), 'username'=>$user->getUsername()], $property);
        $file->setFiletitle($request->headers->get('X-Filename'));
        $file->setDescription($request->headers->get('X-Description'));
        $file->setCredit($request->headers->get('X-Credit'));
        $file->setMimetype($mimeTypeProvided);
        $this->persist($file);
        $this->fileuploader->persistFile($file);
        $property->addFiles($request->getContent());

        $this->dm->flush();
        return $this->response->created($entity, ['entity_basic', 'entity_id', 'user_basic']);
    }

    /**
     * @Route(
     *     "entities/embedded/file/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="get_file",
     *     methods={"GET"})
     */
    public function getFileFromEmbeddedDocumentAction(string $uuid, Request $request)
    {
        $propertyFile = $this->dm->getRepository(FileProperty::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($propertyFile))
        {
            return $this->response->notFound("Route not found");
        }

        $property = $this->dm->getRepository(Property::class)->findOneBy(['uuid'=>$propertyFile->getProperty()]);
        if(empty($property))
        {
            return $this->response->notFound("Route not found");
        }

        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$property->getEntity()]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $headers = [
            'Content-Type'     => $propertyFile->getMimetype(),
            'Content-Disposition' => 'inline',
            'Content-Length' => strlen($propertyFile->getFile())
        ];

        return new BinaryFileResponse($this->fileuploader->getFile($propertyFile), Response::HTTP_OK, $headers);
    }

    /**
     * @Route(
     *     "entities/embedded/file/{uuid}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}"
     *     },
     *     name="delete_file",
     *     methods={"DELETE"})
     */
    public function deleteFileFromEmbeddedDocumentAction(string $uuid, Request $request)
    {
        $propertyFile = $this->dm->getRepository(FileProperty::class)->findOneBy(['uuid'=>$uuid]);
        if(empty($propertyFile))
        {
            return $this->response->notFound("Route not found");
        }

        $property = $this->dm->getRepository(Property::class)->findOneBy(['uuid'=>$propertyFile->getProperty()]);
        if(empty($property))
        {
            return $this->response->notFound("Route not found");
        }

        $entity = $this->dm->getRepository(Entity::class)->findOneBy(['uuid'=>$property->getEntity()]);
        if(empty($entity))
        {
            return $this->response->notFound("Route not found");
        }

        $user = empty($this->getUser()->getUuidAsString()) ? null : $this->getUser();

        $valid = $this->authorizeRequest->validateRequest($entity, $request->getMethod(), $user);
        if(is_object($valid))
        {
            return $valid;
        }

        $this->fileuploader->deleteFile($propertyFile);
        $property->getFiles()->removeElement($propertyFile);
        $this->dm->remove($propertyFile);
        $this->dm->flush();

        return $this->response->emptyResponse();
    }
}