Tailwind Php
==================
TailwindPHP - use Tailwind CSS in your PHP projects (without npm)

Tailwind PHP allows you to compile TailwindCSS on the fly directly with PHP without dependencies (without npm or postcss).

## Installing

IMPORTANT : Alpha version required linux x64.

[PHP](https://php.net) 8.0+ and [Composer](https://getcomposer.org) are required.


```bash
composer req arnolem/tailwindphp
```
Add execution rights
```bash
chmod +x ./vendor/arnolem/tailwindphp/bin/*
```

## Configuration

1- create a ``tailwind.config.js`` config file 

```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.twig"],
  theme: {
    extend: {},
  },
  plugins: [],
}
```
2-Add a link to the css route 

```html
<!-- base.html.twig -->
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ path('css') }}" >
<!-- //... -->
```

3-Create a css route with TailwindPhp


```php
<?php
// src/Controller/CssController.php
namespace App\Controller;

use Arnolem\TailwindPhp\TailwindPhp;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CssController
{
    #[Route(path: 'style.css', name: 'css')]
    public function index(): Response
    {
        return new Response(
            TailwindPhp::build(),
            Response::HTTP_OK,
            ['Content-Type' => 'text/css']
        );
    }
}
```

Optional-Enable SCSS compilation


```php
<?php
// src/Controller/CssController.php
namespace App\Controller;

use Arnolem\TailwindPhp\TailwindPhp;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CssController
{
    #[Route(path: 'style.css', name: 'css')]
    public function index(): Response
    {
        TailwindPhp::enableScss(true);
        
        $scss = 'YOUR_SCSS_CONTENT with @apply ou theme() function';
        
        return new Response(
            TailwindPhp::build($scss),
            Response::HTTP_OK,
            ['Content-Type' => 'text/css']
        );
    }
}
```

## Credits

- Arnaud Lemercier is based on [Wixiweb](https://wixiweb.fr).

## License

TailwindPHP is licensed under [The MIT License (MIT)](LICENSE).