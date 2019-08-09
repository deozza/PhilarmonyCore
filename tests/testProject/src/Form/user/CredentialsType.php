<?php

namespace Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Form\user;

use Deozza\PhilarmonyCoreBundle\Tests\testProject\src\Document\Credentials;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CredentialsType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $option)
  {
    $builder->add('login')
      ->add('password');
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults([
      'data_class'=>Credentials::class,
      'csrf_protection'=>false
    ]);
  }
}
