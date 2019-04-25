<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Service\DatabaseSchema\DatabaseSchemaLoader;
use Deozza\PhilarmonyBundle\Service\ResponseMaker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

    public function generateAndProcess($formKind, $requestBody, $entityToProcess, $entityKind, $formFields)
    {
        if(!is_object($entityToProcess))
        {
            return;
        }

        $form = $this->form->create(FormType::class, null, ['csrf_protection' => false]);

        $this->formFields = $formFields;

        if($this->formFields === "all")
        {
            $this->formFields = $entityKind['properties'];
        }


        if(!is_object(json_decode($requestBody)))
        {
            $data = $this->saveData($requestBody, $entityToProcess, $formKind);
            return $data;
        }


        foreach($this->formFields as $field)
        {
            $this->addFieldToForm($field, $form, $formKind);
        }

        $data = $this->processData($requestBody, $form, $formKind);

        if(is_a($data, JsonResponse::class))
        {
            return $data;
        }


        foreach ($this->formFields as $key=>$item)
        {
            if(is_array($item))
            {
                $data[$key] = [];

                foreach ($item as $subkey=>$subitem)
                {
                    if(array_key_exists($subitem, $data))
                    {
                        $data[$key][$subitem] = $data[$subitem];
                        unset($data[$subitem]);
                    }
                }
            }
        }

        if(!is_object($data))
        {
            $this->saveData($data, $entityToProcess, $formKind);
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