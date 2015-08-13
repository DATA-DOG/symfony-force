<?php

namespace AppBundle;

use Composer\Script\CommandEvent;

class Composer
{
    public static function misc(CommandEvent $event)
    {
        self::spacingParametersYml($event);
        self::installBinaries($event);
    }

    private static function installBinaries(CommandEvent $event)
    {
        if (!is_dir('bin')) {
            $event->getIO()->write(sprintf('The "bin" directory was not found in %s.', getcwd()));
            return;
        }
        $bins = ['archive', 'reload'];
        foreach ($bins as $binary) {
            if (@symlink($src = '../src/AppBundle/Resources/bin/' . $binary, $dst = 'bin/' . $binary) === false) {
                if (!file_exists($dst)) {
                    $event->getIO()->write(sprintf('Failed to symlink %s from %s.', $src, $dst));
                    return;
                }
                continue;
            }
            $event->getIO()->write(sprintf('Installed binary %s.', $dst));
        }
    }

    private static function spacingParametersYml(CommandEvent $event)
    {
        if (!file_exists($file = 'vendor/incenteev/composer-parameter-handler/Processor.php')) {
            return;
        }

        $content = file_get_contents($file);
        $matches = 0;
        $content = str_replace('Yaml::dump($actualValues, 99)', 'Yaml::dump($actualValues, 99, 2)', $content, $matches);
        if ($matches) {
            file_put_contents($file, $content, LOCK_EX);
            $event->getIO()->write('Updated spacing for incenteev parameters');
        }
    }
}
