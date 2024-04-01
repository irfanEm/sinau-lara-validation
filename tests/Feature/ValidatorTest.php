<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Rules\LowerCase;
use App\Rules\RegistrationRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Validator as ValidationValidator;

class ValidatorTest extends TestCase
{
    public function testValidation()
    {
        $data = [
            "username" => "admin",
            "password" => "admin123",
        ];

        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
    }

    public function testValidationInvalid()
    {
        $data = [
            "username" => "",
            "password" => "",
        ];

        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationValExc()
    {
        $data = [
            "username" => "",
            "password" => "",
        ];

        $rules = [
            "username" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try{
            $validator->validate();
            self::fail("Validation Exception Not Throwed");
        }catch(ValidationException $exception){
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }

    public function testValidationMultipleRules()
    {
        App::setLocale('id');
        $data = [
            "username" => "tes",
            "password" => "tes",
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationValidData()
    {
        $data = [
            "username" => "test@email.com",
            "password" => "tes123456",
            "admin" => true,
            "lainya" => "xxx"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try{
            $valid = $validator->validate();
            Log::info(json_encode($valid, JSON_PRETTY_PRINT));
        }catch(ValidationException $exception){
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }

    public function testValidationInlineMessages()
    {
        $data = [
            "username" => "tes",
            "password" => "tes",
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $messages = [
            "required" => ":attribute wajib diisi",
            "email" => ":attribute tidak valid",
            "min" => ":attribute minimal harus :min karakter",
            "mix" => ":attribute maximal harus :max karakter"
        ];

        $validator = Validator::make($data, $rules, $messages);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }


    public function testValidationAdditionalVal()
    {
        $data = [
            "username" => "test@email.com",
            "password" => "test@email.com",
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"],
        ];

        $messages = [
            "required" => ":attribute wajib diisi",
            "email" => ":attribute tidak valid",
            "min" => ":attribute minimal harus :min karakter",
            "mix" => ":attribute maximal harus :max karakter"
        ];

        $validator = Validator::make($data, $rules, $messages);
        $validator->after(function (ValidationValidator $validator){
            $data = $validator->getData();
            if($data['username'] == $data['password']){
                $validator->errors()->add('password', 'password tidak boleh sama dengan username');
            }
        });

        self::assertNotNull($validator);
        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationCustomeRules()
    {
        App::setLocale('id');
        $data = [
            "username" => "TES@email.com",
            "password" => "tes12345",
        ];

        $rules = [
            "username" => ["required", "email", "max:100", new LowerCase()],
            "password" => ["required", "min:6", "max:20"],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationRegistrationRules()
    {
        App::setLocale('id');
        $data = [
            "username" => "TES@email.com",
            "password" => "TES@email.com",
        ];

        $rules = [
            "username" => ["required", "email", "max:100", new LowerCase()],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationCustomFunctionRules()
    {
        App::setLocale('id');
        $data = [
            "username" => "TES@email.com",
            "password" => "TES@email.com",
        ];

        $rules = [
            "username" => ["required", "email", "max:100", function(string $attribute, string $value, \Closure $fail){
                if(strtolower($value) != $value){
                    $fail("$attribute harus menggunakan lowercase !");
                }
            }],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationClassRules()
    {
        App::setLocale('id');
        $data = [
            "username" => "TES@email.com",
            "password" => "TES@email.com",
        ];

        $rules = [
            "username" => ["required", "email", "max:100", function(string $attribute, string $value, \Closure $fail){
                if(strtolower($value) != $value){
                    $fail("$attribute harus menggunakan lowercase !");
                }
            }],
            "password" => ["required", "min:6", "max:20", Password::min(6)->letters()->numbers()->symbols()],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidationNestedArray()
    {
        $data = [
            "name" => [
                "first" => "Balqis",
                "last" => "FA"
            ],
            "address" => [
                "street" => "Jl.Flamboyan",
                "city" => "Pacalic",
                "country" => "IDN"
            ]
        ];

        $rules = [
            "name.first" => ["required", "max:100"],
            "name.last" => ["max:100"],
            "address.street" => ["required", "max:200"],
            "address.city" => ["required", "max:200"],
            "address.country" => ["required", "max:200"],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
    }

    public function testValidationIndexedArray()
    {
        $data = [
            "name" => [
                "first" => "Balqis",
                "last" => "FA"
            ],
            "address" =>[
                [
                    "street" => "Jl.Flamboyan",
                    "city" => "Pacalic",
                    "country" => "IDN"
                ],
                [
                    "street" => "Jl.Raya Sunyalangu",
                    "city" => "Banyumas",
                    "country" => "IDN"
                ]
            ]
        ];

        $rules = [
            "name.first" => ["required", "max:100"],
            "name.last" => ["max:100"],
            "address.*.street" => ["max:200"],
            "address.*.city" => ["required", "max:200"],
            "address.*.country" => ["required", "max:200"],
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
    }

    public function testLoginSucc()
    {
        $response = $this->post("/form/login",[
            "username" => "irfanEm",
            "password" => "erhaEs"
        ]);

        $response->assertStatus(200);
    }

    public function testLoginFail()
    {
        $response = $this->post("/form/login",[
            "username" => "",
            "password" => ""
        ]);

        $response->assertStatus(400);
    }

    public function testFormSucc()
    {
        $response = $this->post("/form",[
            "username" => "irfanEm",
            "password" => "erhaEs"
        ]);

        $response->assertStatus(200);
    }

    public function testFormFail()
    {
        $response = $this->post("/form",[
            "username" => "",
            "password" => ""
        ]);

        $response->assertStatus(302);
    }
}
