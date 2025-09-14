<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]

class TableLink
{
    public string $color = 'primary';
    public string $href = '';
}
