<?php

namespace App\Services\Subscription;

use App\Enums\RateType;
use App\Models\LessonEnrollment;
use App\Models\Rating;
use App\Models\User;
use App\Services\MasterRating\Exceptions\MasterRatingException;

class MasterRatingService
{
    protected $rating = null;
    protected $referenceCode = null;

    public function __construct(protected User $user)
    {
    }

    public function setMasterRating(?int $rating): self
    {
        $this->rating = $rating;
        return $this;
    }

    public function setReferenceCode(?string $referenceCode): self
    {
        $this->referenceCode = $referenceCode;
        return $this;
    }

    /**
     * @throws MasterRatingException
     */
    public function rate()
    {
        $enrollment = LessonEnrollment::where('reference_code', $this->referenceCode)
            ->where('student_id', $this->user->getKey())
            ->firstOrFail();

        if ($enrollment->schedule->isCompleted() === false) {
            throw new MasterRatingException('You can only rate a master after the lesson is completed');
        }

        if ($enrollment->master_rated_at !== null) {
            throw new MasterRatingException('You have already rated this master');
        }

        $rating = $this->saveMasterRating($enrollment->master_id);

        if ($rating->wasRecentlyCreated) {
            $enrollment->master_rated_at = now();
            $enrollment->is_student_attended = true;
            $enrollment->save();
        } else {
            throw new MasterRatingException('Something went wrong while rating the master');
        }
    }

    public function saveMasterRating($masterId)
    {
        $rateMaster = User::findOrFail($masterId);

        $rating = new Rating;
        $rating->user_id =   $this->user->id;
        $rating->rating  = $this->rating;
        $rating->type    = RateType::Master;

        $rateMaster->ratings()->save($rating);

        return $rating;
    }
}
