<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $skipPasswordValidationIfEmpty = function(FormInterface $form) {
            return $form->getData() === null ? false : ['confirm'];
        };
        $builder
            ->add('firstname', 'text', [
                'label' => 'form.label.user.firstname',
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'label' => 'form.label.user.lastname',
                'required' => true,
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'invalid_message' => 'app.user.password.mismatch',
                'required' => true,
                'first_options'  => ['label' => 'form.label.user.password'],
                'second_options' => ['label' => 'form.label.user.repeat_password'],
                'validation_groups' => $skipPasswordValidationIfEmpty,
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => 'confirm',
            'intention' => 'profile',
        ]);
    }

    public function getName()
    {
        return 'profile';
    }
}
