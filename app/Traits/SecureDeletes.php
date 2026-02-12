<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;

trait SecureDeletes
{
    /**
     * Delete the model:
     * - Soft Delete if there are related records.
     * - Force Delete if there are no related records.
     *
     * @param string ...$relations
     * @return void
     */
    public function secureDelete(string ...$relations): void
    {
        if ($this->hasRelatedRecords(...$relations)) {
            $this->delete();
        } else {
            $this->forceDelete();
        }
    }

    /**
     * Check whether the model has any relation and can be permanently deleted.
     *
     * @param string ...$relations
     * @return bool
     */
    public function canSecureDelete(string ...$relations): bool
    {
        return ! $this->hasRelatedRecords(...$relations);
    }

    /**
     * Internal helper to check for dependencies in relationships.
     *
     * @param string ...$relations
     * @return bool
     */
    protected function hasRelatedRecords(string ...$relations): bool
    {
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $query = $this->{$relation}();

                // Agar relation query available hai
                if ($query instanceof Relation) {
                    // Check if the relation uses SoftDeletes, if so include trashed
                    if (method_exists($query, 'withTrashed')) {
                        $query->withTrashed();
                    }

                    if ($query->exists()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
