<?php

declare(strict_types=1);

namespace App\Models;

class CollabModel extends BaseModel
{
    protected $DBGroup = 'collab';
    protected $table   = 'secondlogin';

    protected $useSoftDeletes = false;
    protected $useTimestamps  = false;

    public function getUserName(string $keylink): ?array
    {
        return $this->where('keylink', $keylink)->first();
    }
}
