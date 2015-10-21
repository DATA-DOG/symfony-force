<?php

namespace AdminBundle\Form;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', [
                'label' => 'user.label.email',
            ])
            ->add('firstname', 'text', [
                'label' => 'user.label.firstname',
            ])
            ->add('lastname', 'text', [
                'label' => 'user.label.lastname',
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'first_options'  => ['label' => 'user.label.password'],
                'second_options' => ['label' => 'user.label.repeat_password'],
            ])
        ;
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}
