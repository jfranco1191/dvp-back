<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $dates = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $likeFilterFields  = [
        'vendor_id',
        'client_id',
        'influencer',
        'campaign_name',
        'live_date',
    ];



    /**
     * @return BelongsTo
     */
    public function ticketStatus(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class);
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function scopeFilter($query, $params)
    {
        if ( isset($params['id'] ) && trim($params['id']) !== '') {
            $query->where('id', '=', $params['id']);
        }
        if ( isset($params['ids'] ) && trim($params['ids']) !== '') {
            $query->whereIn('id', explode(',', $params['ids']));
        }
        if ( isset($params['user_id'] ) && trim($params['user_id']) !== '') {
            $query->where( 'user_id', '=', $params['user_id'] );
        }
        if ( isset($params['ticket_status_id'] ) && trim($params['ticket_status_id']) !== '') {
            $query->where('ticket_status_id', '=', $params['ticket_status_id']);
        }
        if ( isset($params['from'] ) && trim($params['from']) !== '') {
            $query->where('created_at', '>=', $params['from']);
        }
        if ( isset($params['to'] ) && trim($params['to']) !== '') {
            $query->where('created_at', '<=', $params['to']);
        }
        return $query;
    }
}
