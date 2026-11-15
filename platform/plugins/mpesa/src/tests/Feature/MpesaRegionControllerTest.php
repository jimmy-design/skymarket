<?php

namespace Njovu\Mpesa\Tests\Feature;

use Tests\TestCase;
use Botble\ACL\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MpesaRegionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected const TEST_REGION_ID = 1;
    protected const NON_EXISTENT_REGION_ID = 999;
    protected const VALID_PAYMENT_MODES = ['0', '1'];

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create required table if it doesn't exist
        if (!Schema::hasTable('mpesa_region_settings')) {
            Schema::create('mpesa_region_settings', function ($table) {
                $table->id();
                $table->integer('region_id')->unique();
                $table->string('business_shortcode');
                $table->string('passkey');
                $table->string('consumer_key');
                $table->string('consumer_secret');
                $table->enum('payment_mode', ['Sandbox', 'Production'])->default('Sandbox');
                $table->timestamps();
            });
        }

        $this->adminUser = User::factory()->superAdmin()->create();
        $this->actingAs($this->adminUser);
    }

    /** @test */
    public function it_saves_region_settings_successfully()
    {
        // Arrange
        $settings = $this->validSettings();

        // Act
        $response = $this->postJson(
            route('payments.mpesa.regions.save'),
            ['regionId' => self::TEST_REGION_ID, 'settings' => $settings]
        );

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['success', 'message', 'data'])
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('mpesa_region_settings', [
            'region_id' => self::TEST_REGION_ID,
            'business_shortcode' => $settings['businessShortcode'],
            'passkey' => $settings['passkey'],
            'consumer_key' => $settings['consumerKey'],
            'consumer_secret' => $settings['consumerSecret'],
            'payment_mode' => $settings['paymentMode'] === '1' ? 'Production' : 'Sandbox',
        ]);
    }

    /** @test */
    public function it_validates_settings_data()
    {
        // Arrange
        $invalidData = [
            'regionId' => self::TEST_REGION_ID,
            'settings' => [
                'businessShortcode' => '', // Invalid empty value
                'paymentMode' => 'invalid' // Invalid mode
            ]
        ];

        // Act
        $response = $this->postJson(route('payments.mpesa.regions.save'), $invalidData);

        // Assert
        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'settings.businessShortcode',
                'settings.passkey',
                'settings.consumerKey',
                'settings.consumerSecret',
                'settings.paymentMode'
            ]);
    }

    /** @test */
    public function it_retrieves_region_settings_successfully()
    {
        // Arrange
        $settings = $this->validSettings();
        $this->saveTestSettings($settings);

        // Act
        $response = $this->getJson(
            route('payments.mpesa.regions.get', ['regionId' => self::TEST_REGION_ID])
        );

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'businessShortcode',
                'passkey',
                'consumerKey',
                'consumerSecret',
                'paymentMode'
            ])
            ->assertJson($settings);
    }

    /** @test */
    public function it_returns_empty_values_for_non_existent_region()
    {
        // Act
        $response = $this->getJson(
            route('payments.mpesa.regions.get', ['regionId' => self::NON_EXISTENT_REGION_ID])
        );

        // Assert
        $response->assertOk()
            ->assertJson([
                'businessShortcode' => null,
                'passkey' => null,
                'consumerKey' => null,
                'consumerSecret' => null,
                'paymentMode' => null
            ]);
    }

    /** @test */
    public function it_deletes_region_settings_successfully()
    {
        // Arrange
        $this->saveTestSettings($this->validSettings());

        // Act
        $response = $this->deleteJson(
            route('payments.mpesa.regions.delete', ['regionId' => self::TEST_REGION_ID])
        );

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['message', 'data'])
            ->assertJson(['data' => ['regionId' => self::TEST_REGION_ID]]);

        $this->assertDatabaseMissing('mpesa_region_settings', [
            'region_id' => self::TEST_REGION_ID
        ]);
    }

    /** @test */
    public function it_handles_deletion_of_non_existent_region_gracefully()
    {
        // Act
        $response = $this->deleteJson(
            route('payments.mpesa.regions.delete', ['regionId' => self::NON_EXISTENT_REGION_ID])
        );

        // Assert
        $response->assertNotFound()
            ->assertJson([
                'error' => true,
                'message' => 'Settings not found'
            ]);
    }

    private function validSettings(): array
    {
        return [
            'businessShortcode' => '123456',
            'passkey' => 'test_passkey',
            'consumerKey' => 'test_consumer_key',
            'consumerSecret' => 'test_consumer_secret',
            'paymentMode' => '0'
        ];
    }

    private function saveTestSettings(array $settings): void
    {
        // Convert paymentMode value to database enum format
        $paymentMode = $settings['paymentMode'] === '1' ? 'Production' : 'Sandbox';
        
        // Create a test record directly in the database
        DB::table('mpesa_region_settings')->updateOrInsert(
            ['region_id' => self::TEST_REGION_ID],
            [
                'business_shortcode' => $settings['businessShortcode'],
                'passkey' => $settings['passkey'],
                'consumer_key' => $settings['consumerKey'],
                'consumer_secret' => $settings['consumerSecret'],
                'payment_mode' => $paymentMode,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}