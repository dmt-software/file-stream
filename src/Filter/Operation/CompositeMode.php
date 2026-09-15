<?php

declare(strict_types=1);

namespace DMT\FileStream\Filter\Operation;

enum CompositeMode: string
{
    case All = 'all';
    case Any = 'any';
    case None = 'none';
}
