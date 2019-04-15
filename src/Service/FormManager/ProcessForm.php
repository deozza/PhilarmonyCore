<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;

class ProcessForm
{
    use AddFieldTrait;
    use SaveDataTrait;

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
}