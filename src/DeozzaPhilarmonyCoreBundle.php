<?php

namespace Deozza\PhilarmonyCoreBundle;

use Deozza\PhilarmonyCoreBundle\DependencyInjection\DeozzaPhilarmonyCoreExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DeozzaPhilarmonyCoreBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new DeozzaPhilarmonyCoreExtension();
        }
        return $this->extension;
    }
}