<?php

namespace App\Models;

/**
 * Class Complaint
 * @package App\Models
 * @property int    $id
 * @property string $createdAt
 * @property string $text
 * @property string $handlerFullname
 * @property string $actionTaken
 * @property string $actionDeadline
 * @property string $completionDate
 * @property string $suggestions
 * @property string $otherComments
 * relations
 * @property User[]                $users
 * @property BranchAssociated|null $branchAssociated
 * @property ComplaintType[]       $types
 */
class Complaint extends ExtendedModel
{
    protected $table = 'complaints';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('App\\Models\\User', 'users_complaints', 'complaint_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function types()
    {
        return $this->belongsToMany('App\\Models\\ComplaintType', 'complaints_types', 'complaint_id', 'type_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function branchAssociated()
    {
        return $this->belongsTo('App\\Models\\BranchAssociated', 'branch_id');
    }

    public function setBranchIdAttribute($value)
    {
        $this->attributes['branch_id'] = $value != 'null' ? $value : null;
    }
}