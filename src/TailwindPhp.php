<?php

namespace Arnolem\TailwindPhp;

use ScssPhp\ScssPhp\Compiler;
use Symfony\Component\Process\Process;
use Throwable;

class TailwindPhp
{

    private static bool $enableScss = false;

    public static function enableScss(bool $enableScss): void
    {
        self::$enableScss = $enableScss;
    }

    public static function build(?string $css = null, ?string $configFile = null): string
    {

        $binFolder = dirname(__DIR__) . '/bin/';

        if(!$configFile){
            $configFile = 'tailwind.config.js';
        }


        if ($css && self::$enableScss) {
            try {
                $scssCompiler = new Compiler();

                $css          = self::protectTailwindFunctionForScss($css);
                $css          = $scssCompiler->compileString($css)->getCss();
                $css          = self::unprotectTailwindFunctionForScss($css);
            }catch (Throwable $e) {
                return self::error($e->getMessage(), 'To resolve the issue, look for a SCSS syntax error.');
            }
        }

        // Temp filepath
        if ($css) {

            $input = tempnam(sys_get_temp_dir(), 'css_');
            file_put_contents($input, $css);

            $tailwindcss = new Process([
                $binFolder . '/tailwindcss-linux-x64',
                '--no-autoprefixer', // for speed
                '-c',
                $configFile,
                '-i',
                $input,
            ], '..');

        } else {

            $tailwindcss = new Process([
                $binFolder . '/tailwindcss-linux-x64',
                '--no-autoprefixer', // for speed
                '-c',
                $configFile,
            ], '..');

        }

        $status = $tailwindcss->run();


        if($status !== 0){

            $output = str_replace("\n", "\\A", $tailwindcss->getErrorOutput());
            $errors = trim(htmlspecialchars($output, ENT_COMPAT, 'UTF-8'));

            if (str_contains($errors, "Permission denied")) {
                $solution = 'To solve this issue: `chmod +x ./vendor/arnolem/tailwindphp/bin/*`';
            }

            return self::error($errors, $solution ?? null);

        }

        // Delete tmpfile
        unlink($input);

        return $tailwindcss->getOutput();
    }

    private static function error($errors, $solution = null){

        // Detect name (easter eggs)
        //$pattern = '/\/srv\/www\/vhosts\/([^\/]+)\/[^\/]+\/vendor\//';

        $pattern = '/(?:\w+\.)+([^.]+)\.([^.]+)\.intra\.wixiweb\.net$/';
        $domain = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        if (preg_match($pattern, $domain, $matches)) {
            $name = ucfirst(strtolower($matches[1]));
            $solution = "Hi $name 🌈! ". $solution;
        }

        $message = implode('\A', [
            $solution,
            '',
            'TAILWINDPHP errors :',
            $errors,
        ]);

        return <<<CSS
                body > *{
                    display: none;
                }
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
                    content: "$message";
                }
            CSS;
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
