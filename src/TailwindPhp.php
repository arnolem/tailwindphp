<?php
namespace Arnolem\TailwindPhp;

use Symfony\Component\Process\Process;

class TailwindPhp
{
    public function __construct()
    {

    }

    public static function build(): string
    {

        $binFolder = dirname(__DIR__) . '/bin/';

        $tailwindcss = new Process([
            $binFolder.'/tailwindcss-linux-x64',
            '--no-autoprefixer', // for speed
            '-c',
            'tailwind.config.js',
        ], '..');
        $tailwindcss->run();

        return $tailwindcss->getOutput();
    }

}