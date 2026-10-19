<?php
namespace SparroWave\AdvancedCod\Models;
use Botble\Base\Models\BaseModel;
class SettlementEntry extends BaseModel
{
    protected $table = 'advanced_cod_entries';
    protected $guarded = ['id'];
    protected $casts = ['amount_paise'=>'integer','fee_paise'=>'integer','occurred_at'=>'datetime'];
    protected static function booted(): void
    {
        static::updating(fn () => throw new \RuntimeException('Settlement entries are immutable; record a reversal.'));
        static::deleting(fn () => throw new \RuntimeException('Settlement history cannot be deleted.'));
    }
}
