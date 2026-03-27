<?php

namespace App\Domain\Licensing;

enum LicenseDurationType: string
{
    case PERMANENT = 'permanent';
    case SUBSCRIPTION = 'subscription';
    case TRIAL = 'trial';
}
