<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password', 'repeated', [
                'type' => 'password',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Password (again)'],
            ])
            ->add('register', 'submit');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Transfer\RegistrationTransfer',
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'app_bundle_registration_type';
    }
}
