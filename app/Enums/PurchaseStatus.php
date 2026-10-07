<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
