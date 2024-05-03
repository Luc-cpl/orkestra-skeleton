<?php

namespace Orkestra\Skeleton\Maker;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MakerExtension extends AbstractExtension
{
    public function __construct(
        private MakerData $makerData
    ){        
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('maker', $this->makerData(...)),
        ];
    }

    private function makerData(array $data)
    {
        $slug = $data['slug'];
        $title = $data['title'];
        $render = $data['render'];
        $default = $data['default'] ?? '';
        $description = $data['description'] ?? '';
        $validation = $data['validation'] ?? '';

        if ($render) {
            return $this->makerData[$slug];
        }

        $this->makerData->setQuestion(
            slug: $slug,
            title: $title,
            description: $description,
            default: $default,
            validation: $validation,
        );
    }
}
