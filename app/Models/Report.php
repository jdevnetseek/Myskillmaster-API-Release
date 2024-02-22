<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Arr;
use App\Models\ReportCategories;
use Spatie\MediaLibrary\HasMedia;
use App\Enums\MediaCollectionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Models\Traits\InteractsWithReportableTypes;
use Illuminate\Database\Eloquent\Relations\Relation;

class Report extends Model implements HasMedia
{
    use InteractsWithMedia;
    use InteractsWithReportableTypes;

    protected $fillable = [
        'reported_by',
        'description',
        'status'
    ];

    protected $appends = [
        'report_type'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionType::REPORT_ATTACHMENTS)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')->width(254);
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function reportable()
    {
        return $this->morphTo();
    }

    public function reason()
    {
        return $this->belongsTo(ReportCategories::class, 'reason_id');
    }

    public function reasons()
    {
        return $this->belongsToMany(ReportCategories::class, 'report_reasons', 'report_id', 'report_category_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function attachments()
    {
        return $this->hasMany(Media::class, 'model_id')
            ->where('collection_name', MediaCollectionType::REPORT_ATTACHMENTS);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getReportTypeAttribute()
    {
        $type = array_search($this->reportable_type, array_merge($this->getReportableTypes(), Relation::$morphMap));

        if ($type !== false) {
            return $type;
        }

        return $this->reportable_type;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeHasReportType(Builder $query, $types)
    {
        $query->hasMorph('reportable', Arr::only($this->getReportableTypes(), $types));
    }

    public function scopeCategory(Builder $query, ...$categories)
    {
        $query->whereHasMorph('reportable', MasterLesson::class, function ($query) use ($categories) {
            $query->whereHas('category', function ($query) use ($categories) {
                $query->whereIn('id', $categories);
            });
        });
    }

    public function scopeReason(Builder $query, ...$reasons)
    {
        $query->whereHas('reasons', function ($query) use ($reasons) {
            $query->whereIn('id', $reasons);
        });
    }
    
    public function scopeStatus(Builder $query, ...$statuses)
    {
        $query->whereIn('status', $statuses);
    }

    public function scopeDateReported(Builder $query, ...$dateReported)
    {
        $query->whereDate('created_at', $dateReported);
    }

    public function scopeSearch(Builder $query, string $search)
    {
        $query->whereHasMorph('reportable', User::class, function ($query) use ($search) {
            $query->search($search);
        });

        $query->orWhereHas('reporter', function ($query) use ($search) {
            $query->search($search);
        });
    }

    public function scopeSearchLesson(Builder $query, string $search)
    {
        $query->whereHasMorph('reportable', MasterLesson::class, function ($query) use ($search) {
            $query->search($search)
                ->orWhere
                ->searchMaster($search);
        });

        $query->orWhereHas('reporter', function ($query) use ($search) {
            $query->search($search);
        });
    }
}
