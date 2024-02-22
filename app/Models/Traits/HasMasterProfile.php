<?php

namespace App\Models\Traits;

use App\Models\MasterProfile as ModelsMasterProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasMasterProfile
{
    public function masterProfile(): HasOne
    {
        return $this->hasOne(ModelsMasterProfile::class);
    }

    public function setMasterProfile(array $data): ModelsMasterProfile
    {
        $data = collect($data);

        $profile = $this->masterProfile()->firstOrCreate();
        $profile->fill($data->only(['about', 'work_experiences'])->toArray());

        $profile->save();

        if ($data->has('languages')) {
            $profile->setLanguages($data->get('languages'));
        }

        if ($data->has('portfolio')) {
            $profile->addPortfolio($data->get('portfolio'));
        }

        return $profile->fresh();
    }

    public function hasMasterProfile(): bool
    {
        return filled($this->masterProfile());
    }
}
