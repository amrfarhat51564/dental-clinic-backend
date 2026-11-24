<?php

namespace App\Enums;

enum ToothConditionType: string
{
    case Sound = 'sound';
    case Caries = 'caries';
    case Filled = 'filled';
    case Missing = 'missing';
    case Crown = 'crown';
    case RootCanalTreated = 'root_canal_treated';
    case Implant = 'implant';
    case Fractured = 'fractured';
    case Unerupted = 'unerupted';
}