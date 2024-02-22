<?php

namespace Tests\Feature\Controllers\V1\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Place;
use App\Mail\VerifyEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Database\Seeders\PlacesTableSeeder;
use Illuminate\Support\Facades\Storage;
use App\Notifications\VerifyPhoneNumber;
use Database\Seeders\CountriesTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Http\UploadedFile;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function userCanRegisterWithEmail()
    {
        Notification::fake();
        Notification::assertNothingSent();

        $this->json('POST', route('register'), [
            'username'            => $username = $this->faker->username,
            'email'                 => $em = $this->faker->email,
            'password'              => $pw = 'password',
            'password_confirmation' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'username',
                        'email',
                        'created_at',
                        'updated_at'
                    ]
                ],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'username' => $username,
                        'email' => $em
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'username' => $username,
            'email'      => $em,
        ]);

        $user = User::whereEmail($em)->first();
        // token must be auto created
        $this->assertNotNull($user->email_verification_code);

        Notification::assertSentTo(
            $user,
            VerifyEmailNotification::class,
            function ($notification) use ($user) {
                $notifier = $notification->toMail($user);
                return $notifier->user === $user;
            }
        );
    }

    /** @skip */
    public function userCanRegisterWithPhoneNumber()
    {
        Notification::fake();
        Notification::assertNothingSent();

        $this->json('POST', route('register'), [
            'full_name'            => $fn = $this->faker->name,
            'phone_number'          => $pn = '639123456789',
            'password'              => $pw = 'password',
            'password_confirmation' => 'password',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'full_name',
                        'phone_number',
                        'created_at',
                        'updated_at'
                    ]
                ],
            ])
            ->assertJson([
                'data' => [
                    'user' => [
                        'full_name' => $fn,
                        'phone_number' => $pn
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'full_name'   => $fn,
            'phone_number' => $pn,
        ]);

        $user = User::wherePhoneNumber($pn)->first();

        // token must be auto created
        $this->assertNotNull($user->phone_number_verification_code);

        Notification::assertSentTo($user, VerifyPhoneNumber::class);
    }

    /** @test */
    public function validateEmailOnUserRegistration()
    {
        $user = User::factory()->create();
        $pw = 'password';

        // no email field
        $this->json('POST', route('register'), [
            'username'              => $this->faker()->userName(),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // empty email
        $this->json('POST', route('register'), [
            'email'                 => '',
            'username'              => $this->faker()->userName(),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // invalid email format
        $this->json('POST', route('register'), [
            'email'                 => 'not_an_email',
            'username'              => $this->faker()->userName(),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // invalid email length
        $this->json('POST', route('register'), [
            'email'                 => Str::random(256) . '@mail.com',
            'username'              => $this->faker()->userName(),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);

        // email already existed
        $this->json('POST', route('register'), [
            'email'                 => $user->email,
            'username'              => $this->faker()->userName(),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['email'],
            ]);
    }

    /** @skip */
    public function validatePhoneOnUserRegistration()
    {
        $user = User::factory()->create(['phone_number' => '6394512300755', 'email' => null]);
        $fn = $this->faker->name;
        $pw = 'password';

        // no phone_number field
        $this->json('POST', route('register'), [
            'full_name'            => $fn,
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['phone_number'],
            ]);

        // empty phone_number
        $this->json('POST', route('register'), [
            'full_name'            => $fn,
            'phone_number'          => '',
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['phone_number'],
            ]);

        // invalid phone_number format
        $this->json('POST', route('register'), [
            'full_name'            => $fn,
            'phone_number'          => 'not_a_valid_phone_number',
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['phone_number'],
            ]);

        // invalid phone_number length
        $this->json('POST', route('register'), [
            'full_name'            => $fn,
            'phone_number'          => Str::random(256),
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['phone_number'],
            ]);

        // phone_number already existed
        $this->json('POST', route('register'), [
            'full_name'            => $fn,
            'phone_number'          => $user->phone_number,
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['phone_number'],
            ]);
    }

    /** @skip */
    public function validateUserNameOnUserRegistrations()
    {
        $user = User::factory()->create();
        $em = $this->faker->email;
        $pw = 'password';

        // no username field
        $this->json('POST', route('register'), [
            'email'                 => $em,
            'password' => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['username'],
            ]);

        // username already existed
        $this->json('POST', route('register'), [
            'username' => $user->username,
            'email'    => $em,
            'password' => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['username'],
            ]);
    }

    /** @skip */
    public function validateFullNameOnUserRegistration()
    {
        $user = User::factory()->create();
        $em = $this->faker->email;
        $pw = 'password';

        // no first name field
        $this->json('POST', route('register'), [
            'email'                 => $em,
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['full_name'],
            ]);

        // empty first name
        $this->json('POST', route('register'), [
            'full_name'            => '',
            'email'                 => $em,
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['full_name'],
            ]);

        // invalid first name length
        $this->json('POST', route('register'), [
            'full_name'            => Str::random(256),
            'email'                 => $em,
            'password'              => $pw,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['full_name'],
            ]);
    }

    /** @test */
    public function validatePasswordOnUserRegistration()
    {
        $user = User::factory()->create();
        $em = $this->faker->email;
        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();
        $pw = 'password';

        // no password field
        $this->json('POST', route('register'), [
            'first_name'            => $fn,
            'last_name'             => $ln,
            'email'                 => $em,
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password'],
            ]);

        // empty password
        $this->json('POST', route('register'), [
            'first_name'            => $fn,
            'last_name'             => $ln,
            'email'                 => $em,
            'password'              => '',
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password'],
            ]);

        // invalid password length
        $this->json('POST', route('register'), [
            'first_name'            => $fn,
            'last_name'             => $ln,
            'email'                 => $em,
            'password'              => Str::random(7),
            'password_confirmation' => $pw,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password'],
            ]);

        // password confirmation fail
        $this->json('POST', route('register'), [
            'first_name'            => $fn,
            'last_name'             => $ln,
            'email'                 => $em,
            'password'              => $pw,
            'password_confirmation' => 'not_equal_to_password',
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message', 'errors' => ['password_confirmation'],
            ]);
    }

    /** @test */
    public function userCanSetTheirAddressInRegistration()
    {
        $this->seedPlaceData();

        Notification::fake();

        $data = [
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'place_id' => Place::inRandomOrder()->first()->getKey(),
        ];

        $this->postJson(route('register'), $data)
            ->assertOk()
            ->assertJson([
                'data' => [
                    'user' => [
                        'email' => $data['email'],
                        'place_id' => $data['place_id'],
                    ],
                ]
            ]);
    }

    /** @test */
    public function userShouldBeAbleToUploadTheirAvatarOnRegistration()
    {
        Storage::fake();

        $data = [
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'avatar' => UploadedFile::fake()->image('avatar.png')
        ];

        $response = $this->postJson(route('register'), $data)->assertOk();

        $data = $response->getData()->data;

        // check if user has avatar
        $this->assertNotNull(User::find(data_get($data, 'user.id'))->avatar);
    }

    /** @test */
    public function validateAvatarOnRegistration()
    {
        Storage::fake();

        $data = [
            'email' => $this->faker->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $data['avatar'] = 'avatar as string';
        $this->postJson(route('register'), $data)
            ->assertUnprocessable()
            ->assertInvalid(['avatar']);

        // invalid file
        $data['avatar'] = UploadedFile::fake()->create('invalid.txt');
        $this->postJson(route('register'), $data)
            ->assertUnprocessable()
            ->assertInvalid(['avatar']);
    }

    private function seedPlaceData()
    {
        $this->seed(CountriesTableSeeder::class);
        $this->seed(PlacesTableSeeder::class);
    }
}
