<?php

namespace SparroWave\SellerInvoicing\Supports;

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Location\Models\City;
use Botble\Location\Models\Country;
use Botble\Location\Models\State;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LocationResolver
{
    public static function resolveState(mixed $state, mixed $country = null): string
    {
        if (empty($state)) {
            return '';
        }

        $str = trim((string) $state);

        // 1. If numeric ID and Location plugin is installed, fetch from State model
        if (is_numeric($str)) {
            try {
                if (class_exists(State::class)) {
                    $name = State::query()->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
                if (Schema::hasTable('states')) {
                    $name = DB::table('states')->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
            } catch (\Throwable) {
            }
        }

        // 2. Check Indian states code mapping
        $indianStates = [
            '01' => 'Jammu and Kashmir',
            '02' => 'Himachal Pradesh',
            '03' => 'Punjab',
            '04' => 'Chandigarh',
            '05' => 'Uttarakhand',
            '06' => 'Haryana',
            '07' => 'Delhi',
            '08' => 'Rajasthan',
            '09' => 'Uttar Pradesh',
            '10' => 'Bihar',
            '11' => 'Sikkim',
            '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland',
            '14' => 'Manipur',
            '15' => 'Mizoram',
            '16' => 'Tripura',
            '17' => 'Meghalaya',
            '18' => 'Assam',
            '19' => 'West Bengal',
            '20' => 'Jharkhand',
            '21' => 'Odisha',
            '22' => 'Chhattisgarh',
            '23' => 'Madhya Pradesh',
            '24' => 'Gujarat',
            '26' => 'Dadra and Nagar Haveli and Daman and Diu',
            '27' => 'Maharashtra',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman and Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh',
            '38' => 'Ladakh',
            'RJ' => 'Rajasthan',
            'MH' => 'Maharashtra',
            'DL' => 'Delhi',
            'UP' => 'Uttar Pradesh',
            'MP' => 'Madhya Pradesh',
            'GJ' => 'Gujarat',
            'KA' => 'Karnataka',
            'TN' => 'Tamil Nadu',
            'KL' => 'Kerala',
            'WB' => 'West Bengal',
            'HR' => 'Haryana',
            'PB' => 'Punjab',
        ];

        $upper = strtoupper($str);
        if (isset($indianStates[$upper])) {
            return $indianStates[$upper];
        }

        $padded = str_pad($str, 2, '0', STR_PAD_LEFT);
        if (isset($indianStates[$padded]) && is_numeric($str) && ! class_exists(State::class)) {
            return $indianStates[$padded];
        }

        return $str;
    }

    public static function resolveCountry(mixed $country): string
    {
        if (empty($country)) {
            return '';
        }

        $str = trim((string) $country);

        $common = [
            'IN' => 'India',
            'IND' => 'India',
            'US' => 'United States',
            'USA' => 'United States',
            'UK' => 'United Kingdom',
            'GB' => 'United Kingdom',
            'AE' => 'United Arab Emirates',
            'UAE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'SG' => 'Singapore',
            'DE' => 'Germany',
            'FR' => 'France',
        ];

        $upper = strtoupper($str);
        if (isset($common[$upper])) {
            return $common[$upper];
        }

        if (class_exists(EcommerceHelper::class)) {
            try {
                $name = EcommerceHelper::getCountryNameById($str);
                if ($name) {
                    return $name;
                }
            } catch (\Throwable) {
            }
        }

        if (is_numeric($str)) {
            try {
                if (class_exists(Country::class)) {
                    $name = Country::query()->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
                if (Schema::hasTable('countries')) {
                    $name = DB::table('countries')->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
            } catch (\Throwable) {
            }
        }

        return $str;
    }

    public static function resolveCity(mixed $city): string
    {
        if (empty($city)) {
            return '';
        }

        $str = trim((string) $city);

        if (is_numeric($str)) {
            try {
                if (class_exists(City::class)) {
                    $name = City::query()->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
                if (Schema::hasTable('cities')) {
                    $name = DB::table('cities')->where('id', $str)->value('name');
                    if ($name) {
                        return (string) $name;
                    }
                }
            } catch (\Throwable) {
            }
        }

        return $str;
    }
}
