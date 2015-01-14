<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', 'email', [
            'label' => 'form.label.user.email',
            'required' => true,
            'constraints' => [
                new NotBlank(['message' => 'app.user.email.blank']),
                new Email(['message' => 'app.user.email.invalid']),
            ],
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'intention' => 'reset_password',
        ]);
    }

    public function getName()
    {
        return 'reset';
    }
}
