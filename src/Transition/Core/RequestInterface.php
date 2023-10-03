<?php

namespace OxidEsales\MediaLibrary\Transition\Core;

interface RequestInterface
{
    public function isOverlay(): bool;

    public function isPopout(): bool;
}
