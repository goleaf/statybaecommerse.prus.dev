<?php declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

trait ExistsOrTrait
{
    /**
     * Check if records exist, and if not, execute a callback
     */
    public function existsOr(callable $callback): bool
    {
        if ($this instanceof Builder || $this instanceof Relation) {
            $exists = $this->exists();
            
            if (!$exists) {
                $callback();
            }
            
            return $exists;
        }
        
        throw new \InvalidArgumentException('existsOr can only be used on Builder or Relation instances');
    }
}
