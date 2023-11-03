<?php

namespace Arnolem\TailwindPhp;

use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Process\Process;

class TailwindPhp
{

    private static bool $enableScss = false;

    public static function enableScss(bool $enableScss): void
    {
        self::$enableScss = $enableScss;
    }

    public static function build(?string $css = null): string
    {

        $binFolder = dirname(__DIR__) . '/bin/';

        if ($css && self::$enableScss) {
            $css          = self::protectTailwindFunctionForScss($css);
            $scssCompiler = new Compiler();
            $css          = $scssCompiler->compileString($css)->getCss();
            $css          = self::unprotectTailwindFunctionForScss($css);
        }

        // Temp filepath
        if ($css) {

            $input = tempnam(sys_get_temp_dir(), 'css_');
            file_put_contents($input, $css);

            $tailwindcss = new Process([
                $binFolder . '/tailwindcss-linux-x64',
                '--no-autoprefixer', // for speed
                '-c',
                'tailwind.config.js',
                '-i',
                $input,
            ], '..');

        } else {

            $tailwindcss = new Process([
                $binFolder . '/tailwindcss-linux-x64',
                '--no-autoprefixer', // for speed
                '-c',
                'tailwind.config.js',
            ], '..');

        }

        $status = $tailwindcss->run();

        if($status !== 0){
            $errors = 'TAILWINDPHP ERRORS : \A'.trim($tailwindcss->getErrorOutput());
            return <<<CSS
                body:before{
                    white-space: pre;
                    border: 5px solid red;
                    padding: 16px;
                    border-radius: 16px;
                    font-family: Verdana, sans-serif;
                    color: #333333;
                    line-height: 130%;
                    background: #ffffff;
                    display: block;
                    content: "$errors";
                }
            CSS;
        }

        // Delete tmpfile
        unlink($input);

        return $tailwindcss->getOutput();
    }

    private static function protectTailwindFunctionForScss($css): string
    {
        $lines  = explode("\n", $css);
        $result = "";

        foreach ($lines as $line) {
            // Vérifie si la ligne contient "theme()" et la commente
            if (str_contains($line, 'theme(')) {
                $line = '/*TAILWINDPHP ' . trim($line) . '*/';
            }
            $result .= $line . "\n";
        }

        return $result;
    }


    private static function unprotectTailwindFunctionForScss($css): string
    {
        return preg_replace('/\/\*TAILWINDPHP(.*?)\*\//s', '$1', $css);
    }

}