<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Link
{
    public string $color = 'primary';
    public string $href = '""';
}
