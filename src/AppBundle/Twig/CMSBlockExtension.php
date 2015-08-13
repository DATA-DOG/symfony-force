<?php namespace AppBundle\Twig;

use Doctrine\ORM\EntityManager;

/**
 * Class CMSBlockExtension
 * USAGE: {{ include(cms_block('alias')) }}
 */
class CMSBlockExtension extends \Twig_Extension
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repo;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->repo = $em->getRepository('AppBundle:CmsBlock');
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'cms_block';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cms_block', [$this, 'renderBlock'], ['needs_environment' => true]),
        ];
    }

    /**
     * @param \Twig_Environment $twig
     * @param string $alias
     * @return \Twig_Template
     * @throws \Exception
     */
    public function renderBlock(\Twig_Environment $twig, $alias)
    {
        $block = $this->repo->findOneBy(['alias'=>$alias]);
        if (!$block) {
            throw new \InvalidArgumentException(sprintf("Block '%s' could not be found", $alias));
        }

        return $twig->createTemplate($block->getContent());
    }
}
