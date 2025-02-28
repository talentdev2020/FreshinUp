<?php

namespace App\Models\Foodfleet\Document\Template;

use App\Enums\DocumentTemplateStatus;
use App\Models\Foodfleet\Document;
use App\Models\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use FreshinUp\FreshBusForms\Models\User\User;
use Illuminate\Support\Facades\Auth;

/**
 * Class Template
 * @package App\Models\Foodfleet\Document\Template
 *
 * @property int id
 * @property string uuid
 * @property string title
 * @property string description
 * @property string content
 * @property int status_id
 * @property string updated_by_uuid
 * @property \Carbon\Carbon created_at
 * @property \Carbon\Carbon updated_at
 *
 *
 * @property \App\User updatedBy
 * @property Status status
 * @property Document[] documents
 */
class Template extends Model
{
    protected $table = 'document_templates';
    protected $guarded = ['id', 'uuid'];
    use GeneratesUuid;

    const CLIENT_EVENT_AGREEMENT = 'Client event agreement';
    const FLEET_MEMBER_EVENT_AGREEMENT = 'Fleet member event agreement';
    const FLEET_MEMBER_EVENT_CONTRACT = 'Fleet member event contract';

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'template_uuid', 'uuid');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_uuid', 'uuid');
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($template) {
            /** @var \App\User $user */
            $user = Auth::user();
            if ($user) {
                $template->updated_by_uuid = $user->uuid;
            }
        });
        static::updating(function ($template) {
            /** @var \App\User $user */
            $user = Auth::user();
            if ($user) {
                $template->updated_by_uuid = $user->uuid;
            }
        });
    }

    public static function getClientAgreement()
    {
        return self::firstOrCreate([
            'title' => self::CLIENT_EVENT_AGREEMENT,
            'status_id' => DocumentTemplateStatus::PUBLISHED
        ], [
            'description' => self::CLIENT_EVENT_AGREEMENT
        ]);
    }

    public static function getFleetMemberEventContract()
    {
        return self::firstOrCreate([
            'title' => self::FLEET_MEMBER_EVENT_CONTRACT,
            'status_id' => DocumentTemplateStatus::PUBLISHED,
        ], [
            'description' => self::FLEET_MEMBER_EVENT_CONTRACT
        ]);
    }

    public static function getFleetMemberEventAgreement()
    {
        return self::firstOrCreate([
            'title' => self::FLEET_MEMBER_EVENT_AGREEMENT,
            'status_id' => DocumentTemplateStatus::PUBLISHED
        ], [
            'description' => self::FLEET_MEMBER_EVENT_AGREEMENT
        ]);
    }
}
