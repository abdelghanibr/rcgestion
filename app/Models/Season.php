<?php





namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    protected $fillable = [
        'name',
        'date_debut',
        'date_fin',
        'type_season',
    ];

    public const TYPES = [
        'session'   => 'حصة',
        'weekly'    => 'أسبوعي',
        'monthly'   => 'شهري',
        'quarterly' => 'ثلاثي',
        'semester'  => 'سداسي',
        'season'    => 'موسم',
        'ticket'    => 'تذكرة',
    ];
}
