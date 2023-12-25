<?php

namespace ArchiElite\IpBlocker\Models;

use Botble\Base\Models\BaseModel;
use Botble\Base\Models\BaseQueryBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Query\Builder;

class History extends BaseModel
{
    use MassPrunable;

    protected $table = 'ip_blocker_logs';

    protected $fillable = [
        'ip_address',
        'count_requests',
    ];

    public function prunable(): Builder|BaseQueryBuilder
    {
        return static::where('created_at', '<=', Carbon::now()->subMonth());
    }
}
