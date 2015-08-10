<?php

namespace AppBundle\Mailer;

use AppBundle\Entity\MailTemplate;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class Mailer
 */
class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $twig;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param \Swift_Mailer $mailer
     * @param EngineInterface $twig
     * @param EntityManager $em
     * @param string $sender
     */
    function __construct(\Swift_Mailer $mailer, EngineInterface $twig, EntityManager $em, $sender)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->em = $em;
        $this->sender = $sender;
    }

    /**
     * @param ContactInterface $contact
     * @param string $alias
     * @param array $data
     */
    public function user(ContactInterface $contact, $alias, $data = [])
    {
        /** @var MailTemplate $template */
        $template = $this->em->getRepository('AppBundle:MailTemplate')->findOneBy(['alias'=>$alias]);

        if (!$template) {
            throw new \InvalidArgumentException(sprintf("Template %s does not exist", $alias));
        }

        $this->send([$contact->getEmail() => $contact->getFullName()], $template, ['user' => $contact] + $data);
    }

    /**
     * @param MailTemplate $template
     * @param array $data
     * @return string
     */
    protected function render(MailTemplate $template, array $data)
    {
        return $this->twig->render("AppBundle:Mail:template.html.twig", compact('template') + $data);
    }

    /**
     * @param string|array $to
     * @param MailTemplate $template
     * @param array $data
     */
    private function send($to, MailTemplate $template, array $data = [])
    {
        $body = $this->render($template, $data);

        $message = new \Swift_Message($template->getSubject(), $body, "text/html", "utf8");
        $message->setFrom($this->sender);
        $message->setTo($to);

        $this->mailer->send($message);
    }
}
