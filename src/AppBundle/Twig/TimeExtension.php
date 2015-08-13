<?php

namespace AppBundle\Twig;

use Symfony\Component\Translation\TranslatorInterface;

class TimeExtension extends \Twig_Extension
{
    protected $translator;

    /**
     * Constructor
     *
     * @param  TranslatorInterface $translator Translator used for messages
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions()
    {
        $time_diff = new \Twig_Function_Method($this, 'diff', ['is_safe' => ['html']]);
        return compact('time_diff');
    }

    public function diff($since = null, $to = null)
    {
        foreach (['since', 'to'] as $var) {
            if ($$var instanceof \DateTime) {
                continue;
            }
            if (is_integer($$var)) {
                $$var = date('Y-m-d H:i:s', $$var);
            }
            $$var = new \DateTime($$var);
        }

        static $units = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        ];

        $diff = $to->diff($since);
        foreach ($units as $attr => $unit) {
            $count = $diff->{$attr};
            if (0 !== $count) {
                $id = sprintf('%s.%s', $diff->invert ? 'ago' : 'in', $unit);
                return $this->translator->transChoice($id, $count, ['%count%' => $count], 'time');
            }
        }
        return $this->translator->trans('empty', [], 'time');
    }

    public function getName()
    {
        return 'time';
    }
}
