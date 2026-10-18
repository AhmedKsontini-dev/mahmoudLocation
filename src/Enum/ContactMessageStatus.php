<?php

namespace App\Enum;

enum ContactMessageStatus: string
{
    case SENT = 'sent';
    case DRAFT = 'draft';
    case SENDING = 'sending';
    case FAILED = 'failed';
}
