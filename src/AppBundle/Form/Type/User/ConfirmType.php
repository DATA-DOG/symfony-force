<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', [
                'label' => 'form.confirm.firstname',
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'label' => 'form.confirm.lastname',
                'required' => true,
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'invalid_message' => 'app.user.password.mismatch',
                'required' => true,
                'first_options'  => ['label' => 'form.confirm.password'],
                'second_options' => ['label' => 'form.confirm.repeat_password'],
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => 'confirm',
            'intention' => 'confirm',
        ]);
    }

    public function getName()
    {
        return 'confirm';
    }
}
