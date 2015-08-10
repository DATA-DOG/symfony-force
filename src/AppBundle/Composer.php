<?php

namespace AppBundle;

use Composer\Script\CommandEvent;

class Composer
{
    public static function installBinaries(CommandEvent $event)
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
}
