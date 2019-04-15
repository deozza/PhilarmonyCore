<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProcessForm
{
    public function __construct(ResponseMaker $responseMaker, FormErrorSerializer $serializer, FormFactoryInterface $formFactory, DatabaseSchemaLoader $schemaLoader, EntityManagerInterface $em)
    {
        $this->response = $responseMaker;
        $this->serializer = $serializer;
        $this->form = $formFactory;
        $this->schemaLoader = $schemaLoader;
        $this->em = $em;
    }

    public function generateAndProcess($formKind, $requestBody, $entityToProcess, $entityKind, $formFields = null)
    {
        if(!is_object($entityToProcess))
        {
            return;
        }

        $isAnEntity = is_a($entityToProcess, Entity::class);

        $form = $this->form->create(FormType::class);

        if($formFields === null)
        {
            $formFields = $entityKind['post']['properties'];

            if($formFields === "all")
            {
                $formFields = $entityKind['properties'];
            }
        }

        if(is_object(json_decode($requestBody)) && !$isAnEntity)
        {
            $data = $this->saveData($requestBody, $entityToProcess);
            return $data;
        }

        foreach($formFields as $field)
        {
            $this->addFieldToForm($field, $form, $isAnEntity);
        }


        $data = $this->processData($requestBody, $form, $formKind);

        if(!is_object($data))
        {
            $this->saveData($data, $entityToProcess);
        }

        return $data;
    }

    private function addFieldToForm($field, $form, $isAnEntity)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($field);

        $this->type = explode(".", $property['type']);
        $class = FieldTypes::ENUMERATION[$this->type[0]];
        $constraints = [];
        $formOptions = [];
        if($property['required'])
        {
            $constraints[] = new NotBlank();
        }

        if(!$isAnEntity)
        {
            $field = "value";
        }

        switch($class)
        {
            case EntityType::class:
                {
                    $formOptions['class'] = Entity::class;
                    $formOptions['query_builder'] = function(EntityRepository $er)
                    {
                        return $er->createQueryBuilder('e')
                            ->where("e.kind = :kind")
                            ->setParameter(':kind', $this->type[1]);
                    };
                };break;

            case ChoiceType::class:
                {
                    $enumeration = $this->schemaLoader->loadEnumerationEnumeration($this->type[1]);
                    $formOptions['choices'] = $enumeration;
                };break;

            case FileType::class: return;break;
            default:
                {
                    if(array_key_exists('length', $property) && !empty($property['length']))
                    {
                        $length = [];
                        if(array_key_exists('min', $property['length']) && !empty($property['length']['min']))
                        {
                            $length['min'] = $property['length']['min'];
                        }
                        if(array_key_exists('max', $property['length']) && !empty($property['length']['max']))
                        {
                            $length['max'] = $property['length']['max'];
                        }

                        $constraints[] = new Length($length);
                    }
                };break;
        }
        $formOptions['constraints'] = $constraints;

        $form->add($field, $class, $formOptions);
    }

    private function processData($data, $form, $formKind)
    {
        $data = json_decode($data, true);
        $form->submit($data, $formKind==="post");

        if(!$form->isValid())
        {
            return $this->response->badRequest($this->serializer->convertFormToArray($form));
        }

        return $form->getData();
    }

    private function saveData($data, $entityToProcess)
    {
        if (is_a($entityToProcess, Entity::class)) {
           return $this->saveEntity($data, $entityToProcess);
        } else {
           return $this->saveProperty($data, $entityToProcess);
        }

    }

    private function saveEntity($data, $entityToProcess)
    {
        $this->em->persist($entityToProcess);

        foreach($data as $key=>$value)
        {
            if(!empty($value))
            {
                $property = new Property();
                $property->setKind($key);
                $property->setEntity($entityToProcess);

                $property->setValue($value);
                $this->em->persist($property);
            }
        }

        return $data;
    }

    private function saveProperty($data, $entityToProcess)
    {
        $propertySchema = $this->schemaLoader->loadPropertyEnumeration($entityToProcess->getKind());

        if(array_key_exists('constraints', $propertySchema))
        {
            if(array_key_exists('mime', $propertySchema['constraints']))
            {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->buffer($data);

                if(!in_array($mimeType, $propertySchema['constraints']['mime']))
                {
                    return $this->response->badRequest("The file must be of one the following types ".json_encode($propertySchema['constraints']['mime']));
                }

                $data = ['value' => base64_encode($data)];
            }
        }

        $entityToProcess->setValue($data['value']);
        $this->em->persist($entityToProcess);

        return true;
    }
}