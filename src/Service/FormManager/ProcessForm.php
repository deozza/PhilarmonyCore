<?php
namespace Deozza\PhilarmonyBundle\Service\FormManager;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Deozza\PhilarmonyBundle\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProcessForm
{
    const FIELD_CLASS = [
        "string" => TextType::class,
        "date" => DateType::class,
        "int" => IntegerType::class,
        "price" => MoneyType::class,
        "enumeration" => ChoiceType::class,
        "entity" => EntityType::class
    ];
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

        foreach($formFields as $field)
        {
            $this->addFieldToForm($field, $form, $isAnEntity);
        }

        $data = $this->processData($requestBody, $form, $formKind);

        if(!is_object($data) && $isAnEntity)
        {
            $this->saveData($data, $entityToProcess);
        }

        return $data;
    }

    private function addFieldToForm($field, $form, $isAnEntity)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($field);

        $this->type = explode(".", $property['type']);
        $class = self::FIELD_CLASS[$this->type[0]];
        $constraints = [];

        if($property['required'])
        {
            $constraints[] = new NotBlank();
        }

        if(!$isAnEntity)
        {
            $field = "value";
        }

        if($class == EntityType::class)
        {
            $form->add($field, $class, [
                'constraints' => $constraints,
                'class' => Entity::class,
                'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('e')
                        ->where("e.kind = :kind")
                        ->setParameter(':kind', $this->type[1]);
                }
            ]);
        }
        elseif($class == ChoiceType::class)
        {
            $enumeration = $this->schemaLoader->loadEnumerationEnumeration($this->type[1]);

            $form->add($field, $class, [
                'constraints' =>$constraints,
                'choices' => $enumeration
            ]);
        }
        else
        {

            $form->add($field, $class, [
                'constraints' =>$constraints,
            ]);
        }

    }

    private function processData($data, $form, $formKind)
    {
        $data = json_decode($data, true);
        $form->submit($data, $formKind==="post");

        if(!$form->isValid())
        {
            return $this->response->badRequest([
                'status'=>'error',
                'errors'=>$this->serializer->convertFormToArray($form)
            ]);
        }

        return $form->getData();
    }

    private function saveData($data, $entityToProcess)
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
    }
}