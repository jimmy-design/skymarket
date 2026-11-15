<?php

namespace Njovu\Mpesa\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Validation\Rule;
use Botble\Location\Http\Resources\StateResource;
use Botble\Location\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MpesaRegionController extends Controller
{
    public function saveRegionSettings(Request $request)
    {
        $validated = $request->validate([
            'regionId' => 'required|integer|min:1',
            'settings.businessShortcode' => 'required|string|max:255',
            'settings.passkey' => 'required|string|max:255',
            'settings.consumerKey' => 'required|string|max:255',
            'settings.consumerSecret' => 'required|string|max:255',
            'settings.paymentMode' => ['required', Rule::in(['0', '1'])],
        ]);

        $regionId = $request->input('regionId');
        $settings = $request->input('settings');

        // Log payment mode value before conversion
        Log::info('Received payment mode for region settings', [
            'regionId' => $regionId,
            'paymentMode' => $settings['paymentMode'],
            'paymentModeType' => gettype($settings['paymentMode'])
        ]);

        // Convert paymentMode value to database enum format - now using 'sandbox'/'live'
        $paymentMode = $settings['paymentMode'] === '1' ? 'live' : 'sandbox';

        Log::info('Converted payment mode for database', [
            'originalValue' => $settings['paymentMode'],
            'convertedValue' => $paymentMode
        ]);

        // Check if a record already exists for this region
        $existingRecord = DB::table('mpesa_region_settings')
            ->where('region_id', $regionId)
            ->first();

        if ($existingRecord) {
            // Update existing record
            Log::info('Updating existing region settings', [
                'regionId' => $regionId,
                'previousPaymentMode' => $existingRecord->payment_mode,
                'newPaymentMode' => $paymentMode
            ]);

            DB::table('mpesa_region_settings')
                ->where('region_id', $regionId)
                ->update([
                    'business_shortcode' => $settings['businessShortcode'],
                    'passkey' => $settings['passkey'],
                    'consumer_key' => $settings['consumerKey'],
                    'consumer_secret' => $settings['consumerSecret'],
                    'payment_mode' => $paymentMode,
                    'updated_at' => now(),
                ]);
        } else {
            // Create new record
            Log::info('Creating new region settings', [
                'regionId' => $regionId,
                'paymentMode' => $paymentMode
            ]);

            DB::table('mpesa_region_settings')->insert([
                'region_id' => $regionId,
                'business_shortcode' => $settings['businessShortcode'],
                'passkey' => $settings['passkey'],
                'consumer_key' => $settings['consumerKey'],
                'consumer_secret' => $settings['consumerSecret'],
                'payment_mode' => $paymentMode,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
            'data' => ['regionId' => $regionId]
        ]);
    }
    
    public function getRegionSettings($regionId)
    {
        // Find the settings for this region
        $regionSettings = DB::table('mpesa_region_settings')
            ->where('region_id', $regionId)
            ->first();

        if (!$regionSettings) {
            // Return empty settings if none found
            return response()->json([
                'businessShortcode' => null,
                'passkey' => null,
                'consumerKey' => null,
                'consumerSecret' => null,
                'paymentMode' => null,
            ]);
        }

        // Convert payment_mode from database enum ('sandbox'/'live') to '0'/'1' format for frontend
        $paymentMode = $regionSettings->payment_mode === 'live' ? '1' : '0';

        // Format response to match expected structure
        return response()->json([
            'businessShortcode' => $regionSettings->business_shortcode,
            'passkey' => $regionSettings->passkey,
            'consumerKey' => $regionSettings->consumer_key,
            'consumerSecret' => $regionSettings->consumer_secret,
            'paymentMode' => $paymentMode,
        ]);
    }

    public function deleteRegionSettings($regionId, BaseHttpResponse $response)
    {
        // Find the settings for this region
        $exists = DB::table('mpesa_region_settings')
            ->where('region_id', $regionId)
            ->exists();

        if (!$exists) {
            return $response->setError()
                ->setCode(404)
                ->setMessage('Settings not found');
        }

        // Delete the settings
        DB::table('mpesa_region_settings')
            ->where('region_id', $regionId)
            ->delete();

        return $response
            ->setMessage('Settings deleted successfully')
            ->setData(['regionId' => $regionId]);
    }

    // ajax.states-by-country
    // ajax.cities-by-state
    // platform/plugins/location/src/Http/Controllers/StateController.php
    public function getStatesByCoId(Request $request): JsonResponse
    {
        $data = State::query()
            ->select(['id', 'name'])
            ->wherePublished()
            ->orderBy('order')
            ->orderBy('name');

        $countryId = $request->input('country_id');

        if ($countryId && $countryId != 'null') {
            $data = $data->where('country_id', $countryId);
        }

        $data = $data->get();

        // Return a JSON response with the data
        return response()->json([
            'data' => StateResource::collection($data),
            'message' => 'States retrieved successfully.',
            'status' => true,
        ]);
    }
}