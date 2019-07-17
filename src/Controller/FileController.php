<?php
namespace Deozza\PhilarmonyCoreBundle\Controller;

use Deozza\PhilarmonyCoreBundle\Controller\BaseController;
use Deozza\PhilarmonyCoreBundle\Entity\Entity;
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
     *     "entity/{uuid}/{file_property}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "file_property" = "^(\w{1,50})$"
     *     },
     *     name="get_file",
     *      methods={"GET"})
     */
    public function getFileAction(string $uuid,string $file_property, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        $valid = $this->validateRequest($entity, $request->getMethod());
        if(is_object($valid))
        {
            return $valid;
        }
        $properties = $entity->getProperties();
        if(!array_key_exists($file_property, $properties) || empty($properties[$file_property]))
        {
            return $this->response->notFound("Resource not found");
        }

        $files = $properties[$file_property];
        $filename = $request->headers->get('X-File-Name');
        if(!empty($filename))
        {
            if(!array_key_exists($filename, $files))
            {
                return $this->response->notFound("Resource not found");
            }

            $files = $files[$filename];
        }

        return $this->response->ok($files);
    }

    /**
     * @Route(
     *     "entity/{uuid}/{file_property}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "file_property" = "^(\w{1,50})$"
     *     },
     *     name="post_file",
     *      methods={"POST"})
     */
    public function postFileAction(string $uuid,string $file_property, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        $valid = $this->validateRequest($entity, $request->getMethod());
        if(is_object($valid))
        {
            return $valid;
        }
        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];
        $entityConfig = $entityStates[$entity->getValidationState()]['methods'][$request->getMethod()];

        if(!in_array($file_property, $entityConfig['properties']))
        {
            return $this->response->notFound("Resource not found");
        }

        $property = $this->schemaLoader->loadPropertyEnumeration($file_property);
        if($property['type'] !== 'file')
        {
            return $this->response->notFound("Resource not found");
        }

        if(empty($request->getContent()))
        {
            return $this->response->badRequest("Request must not be empty. A raw data file must be provided");
        }

        $alreadyDefined = array_key_exists($file_property, $entity->getProperties()) && !empty($entity->getProperties()[$file_property]);
        $allowMultiple = array_key_exists('array', $property) && $property['array'] === true;

        if($allowMultiple === false && $alreadyDefined === true)
        {
            return $this->response->badRequest("Property already exists");
        }
        $file_info = new \finfo(FILEINFO_MIME_TYPE);
        $mimeTypeProvided = $file_info->buffer($request->getContent());

        if(!in_array($mimeTypeProvided,$property['constraints']['mime']))
        {
            return $this->response->badRequest("Bad mimetype. Accepted files are : ".json_encode($property['constraints']['mime']));
        }

        $content = base64_encode($request->getContent());
        $filename = null;
        if(!empty($request->headers->get('X-File-Name')))
        {
            $filename = $request->headers->get('X-File-Name');
            $filename = mb_substr($filename, 0,50);
        }

        $properties = $entity->getProperties();
        if($alreadyDefined === false)
        {
            $properties[$file_property] = [empty($filename) ? "0" : $filename => $content];
        }
        else
        {
            $properties[$file_property] += [empty($filename) ? count($properties[$file_property]) : $filename => $content];
        }

        $entity->setProperties($properties);

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());

        if($entity->getValidationState() !== "__default")
        {
            $this->em->flush();
        }

        if(is_array($state))
        {
            return $this->response->conflict($state, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }

        $this->handleEvents($request->getMethod(), $entity['states']['__default'], $entity, $eventDispatcher);

        $this->em->flush();

        return $this->response->created($entity, ['entity_complete', 'user_basic']);
    }

    /**
     * @Route(
     *     "entity/{uuid}/{file_property}",
     *     requirements={
     *          "uuid" = "[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}",
     *          "file_property" = "^(\w{1,50})$"
     *     },
     *     name="delete_file",
     *      methods={"DELETE"})
     */
    public function deleteFileAction(string $uuid,string $file_property, Request $request, EventDispatcherInterface $eventDispatcher)
    {
        $entity = $this->em->getRepository(Entity::class)->findOneByUuid($uuid);
        $valid = $this->validateRequest($entity, $request->getMethod());
        if(is_object($valid))
        {
            return $valid;
        }
        $properties = $entity->getProperties();
        if(!array_key_exists($file_property, $properties) || empty($properties[$file_property]))
        {
            return $this->response->notFound("Resource not found");
        }

        $files = $properties[$file_property];
        $filename = $request->headers->get('X-File-Name');
        if(!empty($filename))
        {
            if(!array_key_exists($filename, $files))
            {
                return $this->response->notFound("Resource not found");
            }

            $files = $files[$filename];
        }

        $entityStates = $this->schemaLoader->loadEntityEnumeration($entity->getKind())['states'];

        if(is_string($files))
        {
            unset($properties[$file_property][$filename]);
        }
        else
        {
            unset($properties[$file_property]);
        }

        $entity->setProperties($properties);

        $state = $this->validate->processValidation($entity,0, $entityStates, $this->getUser());
        if($entity->getValidationState() !== "__default")
        {
            $this->em->flush();
        }
        if(is_array($state))
        {
            return $this->response->conflict($state, $entity, ['entity_id', 'entity_property', 'entity_basic']);
        }

        $this->handleEvents($request->getMethod(), $entity['states']['__default'], $entity, $eventDispatcher);

        $this->em->flush();

        return $this->response->empty();
    }
}
