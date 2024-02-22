<?php

namespace App\Models;

use App\Enums\UsernameType;
use Laravel\Cashier\Billable;
use App\Observers\UserObserver;
use App\Support\ValidatesPhone;
use App\Models\Traits\HasAvatar;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use App\Models\Traits\CanBeReported;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\ManagesStripeCards;
use Illuminate\Database\Eloquent\Builder;
use App\Support\Faker\BypassCodeValidator;
use App\Models\Traits\ManagesOneTimePassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use NotificationChannels\Twilio\TwilioChannel;
use App\Models\Traits\InteractsWithChangeRequest;
use App\Models\Traits\ManagesStripeConnectAccount;
use App\Models\Traits\InteractsWithVerificationToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Interfaces\Notifiable as NotifiableInterface;
use App\Models\Traits\HasMasterProfile;
use App\Models\Traits\HasLessonPreferences;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\Traits\HasMasterLesson;
use App\Models\Traits\HasPayouts;
use App\Models\Traits\HasSubscription;
use App\Models\Traits\InteractsWithReport;
use App\Models\Traits\Rateable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string full_name
 */
class User extends Authenticatable implements MustVerifyEmail, HasMedia, NotifiableInterface
{
    use Billable;
    use HasRoles;
    use HasFactory;
    use HasAvatar;
    use HasLessonPreferences;
    use HasPayouts;
    use Notifiable;
    use HasApiTokens;
    use CanBeReported;
    use ManagesStripeCards;
    use ManagesStripeConnectAccount;
    use ManagesOneTimePassword {
        hasValidOneTimePassword as traitHasValidOneTimePassword;
    }
    use InteractsWithChangeRequest;
    use InteractsWithVerificationToken;
    use InteractsWithReport;
    use ValidatesPhone;
    use BypassCodeValidator;
    use HasMasterProfile;
    use HasMasterLesson;
    use HasSubscription;
    use Rateable;

    /**
     * The default guard to use
     * This will use by laravel permission package to determine the guard to use
     *
     * @var string
     */
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'phone_number',
        'birthdate',
        'email_verified_at',
        'phone_number_verified_at',
        'place_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
        'phone_number_verification_code',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at'        => 'datetime',
        'phone_number_verified_at' => 'datetime',
        'onboarded_at'             => 'datetime'
    ];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)
            ->withBlocked()
            ->first();
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // register model observer
        static::observe(UserObserver::class);

        /**
         * Filter users who are blocked by default.
         */
        static::addGlobalScope('blocked', function (Builder $query) {
            $query->whereNull('blocked_at');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * User linked social media accounts
     *
     */
    public function linkedAccounts()
    {
        return $this->hasMany(LinkedAccount::class, 'user_id');
    }

    /**
     * User password reset entry
     *
     */
    public function passwordReset()
    {
        return $this->hasOne(PasswordReset::class, 'user_id');
    }

    /**
     * User devices
     *
     */
    public function devices()
    {
        return $this->hasMany(Device::class, 'user_id');
    }

    /**
     * The List of reports that the user fileds against.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function filedReports()
    {
        return $this->hasMany(Report::class, 'reported_by');
    }

    /**
     * This list of jobs posted by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function jobs()
    {
        return $this->hasMany(Job::class, 'author_id');
    }

    /**
     * User to Posts relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    /**
     * The List of ratings that the user submitted.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function submittedRatings()
    {
        return $this->hasMany(Rating::class, 'user_id');
    }


    public function enrolledLessons(): HasMany
    {
        return $this->hasMany(LessonEnrollment::class, 'student_id');
    }

    public function paymentHistories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class, 'user_id');
    }

    /*
     * Lesson enrollments relationship where the user is the master
     */
    public function enrollmentsAsMaster()
    {
        return $this->hasMany(LessonEnrollment::class, 'master_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Search from name, email or phone number
     *s
     */
    public function scopeSearch($query, $search)
    {
        $query->where(function ($query) use ($search) {
            $query->where('first_name', 'LIKE', "%$search%")
                ->orWhere('last_name', 'LIKE', "%$search%")
                ->orWhere('email', 'LIKE', "%$search%")
                ->orWhere('phone_number', 'LIKE', "%$search%");
        });
    }

    /**
     * Include the users that where blocked.
     */
    public function scopeWithBlocked(Builder $query)
    {
        $query->withoutGlobalScope('blocked');
    }

    /**
     * Filter the result of the query by users that where blocked only.
     */
    public function scopeOnlyBlocked(Builder $query)
    {
        $query->withoutGlobalScope('blocked');
        $query->whereNotNull('blocked_at');
    }

    /**
     * Filter the result of the query by users that where blocked only.
     */
    public function scopeOnlyNonOnBoarded(Builder $query)
    {
        $query->whereNull('onboared_at');
    }

    /**
     * Query user by username
     *
     * @param Builder $query
     * @param string $username
     */
    public function scopeHasUsername(Builder $query, string $username): void
    {
        $query->where('email', $username);
    }

    /**
     * Filter the result of the query by users that where subscribe.
     */
    public function scopeOnlyMaster(Builder $query)
    {
        $query->whereHas('subscriptions', function ($query) {
            $query->active();
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Mutator methods
    |--------------------------------------------------------------------------
    */

    /**
     * Remove the plus (+) sign for every phone number
     *
     * @param string $value
     * @return void
     */
    public function setPhoneNumberAttribute(?string $value)
    {
        $this->attributes['phone_number'] = $this->cleanPhoneNumber($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor methods
    |--------------------------------------------------------------------------
    */

    /**
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $fn = $attributes['first_name'] ?? '';
                $ln = $attributes['last_name'] ?? '';

                return trim(ucwords($fn . ' ' . $ln));
            }
        );
    }


    /**
     * verified phone number or email
     *
     * @return String Phone Number or Email
     */
    public function getVerifiedAccountAttribute()
    {
        return $this->isEmailVerified() ? $this->email : $this->phone_number;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if user can request payout to their Stripe balance
     */
    public function canRequestPayout(): bool
    {
        return $this->payouts_enabled && $this->isReportedLessonsResolved();
    }

    /**
     * Check if code is valid
     *
     * @param string $code
     * @return boolean
     */
    public function isValidEmailVerificationCode($code)
    {
        /** on debug mode, allow bypass for token validation */
        if ($this->isUsingBypassCode($code)) {
            return true;
        }

        return $code === $this->email_verification_code;
    }

    /**
     * Check if code is valid
     *
     * @param string $code
     * @return boolean
     */
    public function isValidPhoneVerificationCode($code)
    {
        /** on debug mode, allow bypass for token validation */
        if ($this->isUsingBypassCode($code)) {
            return true;
        }

        return $code === $this->phone_number_verification_code;
    }

    /**
     * Check if the user email or phone number is verified
     *
     * @return boolean
     */
    public function isVerified(): bool
    {
        return filled($this->email_verified_at) || filled($this->phone_number_verified_at);
    }

    /**
     * Check if the user email is verified
     *
     * @return boolean
     */
    public function isEmailVerified(): bool
    {
        return filled($this->email_verified_at);
    }

    /**
     * Check if the user phone_number is verified
     *
     * @return boolean
     */
    public function isPhoneNumberVerified(): bool
    {
        return filled($this->phone_number_verified_at);
    }

    /**
     * Check if the users account is blocked or disabled.
     *
     * @return boolean
     */
    public function isBlocked(): bool
    {
        return filled($this->blocked_at);
    }

    /**
     * Check if the user has email
     *
     * @return boolean
     */
    public function hasEmail(): bool
    {
        return filled($this->email);
    }

    /**
     * Check if the user has phone_number
     *
     * @return boolean
     */
    public function hasPhoneNumber(): bool
    {
        return filled($this->phone_number);
    }

    /**
     * Check if the user has password
     *
     * @return boolean
     */
    public function hasPassword(): bool
    {
        return filled($this->password);
    }

    /**
     * Checks if the user has been onboarded.
     *
     * @return boolean
     */
    public function isOnboarded(): bool
    {
        return filled($this->onboarded_at);
    }

    /**
     * Mark user as onboarded.
     *
     * @return void
     */
    public function onboard(): void
    {
        if (!$this->isOnboarded()) {
            $this->onboarded_at = now();
            $this->save();
        }
    }

    /**
     * User Default avatar
     *
     * @return string
     */
    public function defaultAvatar(): string
    {
        return asset('/images/default-profile.png');
    }

    /**
     * Helper to mark date of verification for email.
     *
     * @return bool
     */
    public function verifyEmailNow(): bool
    {
        return $this->update(['email_verified_at' => now()]);
    }

    /**
     * Helper to mark date of verification for phone number.
     *
     * @return bool
     */
    public function verifyPhoneNumberNow(): bool
    {
        return $this->update(['phone_number_verified_at' => now()]);
    }

    /**
     * Check if the priramy username is email.
     *
     * @return boolean
     */
    public function isEmailPrimary()
    {
        return $this->primary_username === UsernameType::EMAIL;
    }

    /**
     * Check if the primary username is phone number
     *
     * @return boolean
     */
    public function isPhonePrimary()
    {
        return $this->primary_username === UsernameType::PHONE_NUMBER;
    }
    /*
    |--------------------------------------------------------------------------
    | FCM
    |--------------------------------------------------------------------------
    */

    /**
     * Route notifications for the FCM channel
     * Specifies the user's FCM token
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return array
     */
    public function routeNotificationForFcm($notification): array
    {
        return $this->devices->map(function ($device) {
            return $device->token;
        })->toArray();
    }

    /**
     * Route Notification for TWilio
     *
     * @return string
     */
    public function routeNotificationForTwilio($notification)
    {
        $phone = $this->uncleanPhoneNumber($this->phone_number);

        return $phone;
    }

    /*
    |--------------------------------------------------------------------------
    | One-Time-Password
    |--------------------------------------------------------------------------
    */

    /**
     * Validates if the one time password is correct.
     *
     * If otp is a valid one time password, it will invalidate the old
     * one and return true.
     *
     * @param string $value
     * @return boolean
     */
    public function invalidateIfValidOneTimePassword(string $value): bool
    {
        /** on debug mode, allow bypass for token validation */
        if ($this->isUsingBypassCode($value)) {
            return true;
        }

        if ($this->traitHasValidOneTimePassword($value)) {
            $this->invalidateOneTimePassword();
            return true;
        }

        return false;
    }

    /**
     * The otp channel to be used when sending the otp
     *
     * @return string
     */
    public function otpChannel(): string
    {
        return TwilioChannel::class;
    }

    /**
     * Declare where the otp is sent.
     *
     * @return string
     */
    public function otpDestination(): string
    {
        return $this->uncleanPhoneNumber($this->phone_number);
    }
}
