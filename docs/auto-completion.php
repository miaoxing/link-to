<?php

/**
 * @property    Miaoxing\LinkTo\Service\LinkTo $linkTo
 */
class LinkToMixin
{
}

/**
 * @mixin LinkToMixin
 */
class AutoCompletion
{
}

/**
 * @return AutoCompletion
 */
function wei()
{
    return new AutoCompletion();
}

/** @var Miaoxing\LinkTo\Service\LinkTo $linkTo */
$linkTo = wei()->linkTo;
