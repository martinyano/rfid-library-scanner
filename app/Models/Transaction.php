<?php

namespace App\Models;

/**
 * ERD-facing alias for a transaction/loan record.
 */
class Transaction extends Borrowing
{
    protected $table = 'transactions';
}
