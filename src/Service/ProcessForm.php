<?php
namespace Deozza\PhilarmonyBundle\Service;

use Deozza\PhilarmonyBundle\Entity\Entity;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function __construct(ResponseMaker $responseMaker, FormErrorSerializer $serializer, FormFactoryInterface $formFactory, DatabaseSchemaLoader $schemaLoader)
    {
        $this->response = $responseMaker;
        $this->serializer = $serializer;
        $this->form = $formFactory;
        $this->schemaLoader = $schemaLoader;
    }

    public function simpleProcess(Request $request, $formClass, $entity, $options = [])
    {
        if(!is_object($entity))
        {
            return;
        }
        $form = $this->form->create($formClass, $entity, $options);
        return $this->processData($request->getContent(), $form);
    }

    public function generateAndProcess($requestBody, $newEntity, $entity)
    {
        if(!is_object($newEntity))
        {
            return;
        }

        $form = $this->form->create(FormType::class);

        $form_fields = $entity['post']['properties'];

        if($form_fields === "all")
        {
            $form_fields = $entity['properties'];
        }

        foreach($form_fields as $field)
        {
            $this->addFieldToForm($field, $form);
        }

        return $this->processData($requestBody, $form);
    }

    private function addFieldToForm($field, $form)
    {
        $property = $this->schemaLoader->loadPropertyEnumeration($field);
        $this->type = explode(".", $property['type']);
        $class = self::FIELD_CLASS[$this->type[0]];
        $constraints = [];

        if($property['required'])
        {
            $constraints[] = new NotBlank();
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

    private function processData($data, $form)
    {
        $data = json_decode($data, true);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->response->badRequest([
                'status'=>'error',
                'errors'=>$this->serializer->convertFormToArray($form)
            ]);
        }


        return $form->getData();
    }
}