<?php

namespace App\Enum;

enum ContactMessageStatus: string
{
    case SENT = 'sent';
    case DRAFT = 'draft';
}
