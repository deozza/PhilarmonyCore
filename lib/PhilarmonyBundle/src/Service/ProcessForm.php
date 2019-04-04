<?php
namespace Deozza\PhilarmonyBundle\Service;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class ProcessForm
{

    public function __construct(ResponseMaker $responseMaker, FormErrorSerializer $serializer, FormFactoryInterface $formFactory)
    {
        $this->response = $responseMaker;
        $this->serializer = $serializer;
        $this->form = $formFactory;
    }

    public function process(Request $request, $formClass, $entity, $options = [])
    {
        if(!is_object($entity))
        {
            return;
        }
        $form = $this->form->create($formClass, $entity, $options);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if(!$form->isValid())
        {
            return $this->response->badRequest([
                'status'=>'error',
                'errors'=>$this->serializer->convertFormToArray($form)
            ]);
        }

        return $entity;
    }
}