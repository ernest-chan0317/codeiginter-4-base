<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected bool $hasActiveColumn = false;

    /** @var list<string> */
    protected array $sortableFields = [];

    protected $allowCallbacks = true;
    protected $beforeInsert   = ['setCreatedBy'];
    protected $beforeUpdate   = ['setUpdatedBy'];
    protected $beforeDelete   = ['setDeletedBy'];

    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = array_values(array_unique([
            ...$this->allowedFields,
            'created_by',
            'updated_by',
            'deleted_by',
        ]));
    }

    public function hasActiveColumn(): bool
    {
        return $this->hasActiveColumn;
    }

    public function isSortable(string $field): bool
    {
        return in_array($field, $this->sortableFields, true);
    }

    protected function setCreatedBy(array $eventData): array
    {
        if (auth()->loggedIn()) {
            $eventData['data']['created_by'] = auth()->user()->username;
        }

        return $eventData;
    }

    protected function setUpdatedBy(array $eventData): array
    {
        if (auth()->loggedIn()) {
            $eventData['data']['updated_by'] = auth()->user()->username;
        }

        return $eventData;
    }

    protected function setDeletedBy(array $eventData): array
    {
        if (! $this->useSoftDeletes || ! auth()->loggedIn()) {
            return $eventData;
        }

        $this->db->table($this->table)
            ->where($this->primaryKey, $eventData['id'])
            ->update(['deleted_by' => auth()->user()->username]);

        return $eventData;
    }
}
