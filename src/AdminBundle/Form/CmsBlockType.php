<?php namespace AdminBundle\Form;

use AppBundle\Entity\CmsBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CmsBlockType
 */
class CmsBlockType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('alias', TextType::class, ['label' => 'cms_block.label.alias']);
        $builder->add('name', TextType::class, ['label' => 'cms_block.label.name']);
        $builder->add('content', TextAreaType::class, ['label' => 'cms_block.label.content']);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsBlock::class
        ]);
    }


}
